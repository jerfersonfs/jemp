<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InvoiceItemController extends Controller
{
    /**
     * Lista todos os itens de fatura
     */
    public function index()
    {
        $items = InvoiceItem::with(['invoice', 'product'])->get();

        return response()->json($items);
    }

    /**
     * Guarda um novo item de fatura
     */
    public function store(StoreInvoiceItemRequest $request)
    {
        $invoiceItem = DB::transaction(function () use ($request): InvoiceItem {
            $data = $request->validated();
            Invoice::query()->whereKey($data['invoice_id'])->lockForUpdate()->firstOrFail()->assertItemsEditable();
            $product = Product::query()->findOrFail($data['product_id']);
            $invoiceItem = InvoiceItem::create($data + ['unit_price' => $product->cost_price]);
            $invoiceItem->invoice->recalculateAmount();

            return $invoiceItem->load(['invoice', 'product']);
        });

        return response()->json([
            'message' => 'Item de nota fiscal criado com sucesso.',
            'data' => $invoiceItem,
        ], 201);
    }

    /**
     * Mostra os detalhes de um item específico
     */
    public function show(InvoiceItem $invoiceItem)
    {
        $invoiceItem->load(['invoice', 'product']);

        return response()->json($invoiceItem);
    }

    /**
     * Atualiza um item de fatura existente
     */
    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem)
    {
        $updatedItem = DB::transaction(function () use ($request, $invoiceItem): InvoiceItem {
            $data = $request->validated();
            $previousInvoiceId = $invoiceItem->invoice_id;
            $nextInvoiceId = $data['invoice_id'] ?? $previousInvoiceId;
            $invoices = Invoice::query()->whereIn('invoice_id', [$previousInvoiceId, $nextInvoiceId])
                ->orderBy('invoice_id')->lockForUpdate()->get();
            foreach ($invoices as $invoice) {
                $invoice->assertItemsEditable();
            }
            $nextProductId = $data['product_id'] ?? $invoiceItem->product_id;

            if ((int) $nextProductId !== (int) $invoiceItem->product_id) {
                $data['unit_price'] = Product::query()->findOrFail($nextProductId)->cost_price;
            }

            $invoiceItem->update($data);
            Invoice::query()->findOrFail($previousInvoiceId)->recalculateAmount();
            if ((int) $nextInvoiceId !== (int) $previousInvoiceId) {
                Invoice::query()->findOrFail($nextInvoiceId)->recalculateAmount();
            }

            return $invoiceItem->load(['invoice', 'product']);
        });

        return response()->json([
            'message' => 'Item de nota fiscal atualizado com sucesso.',
            'data' => $updatedItem,
        ], 200);
    }

    /**
     * Remove um item de fatura
     */
    public function destroy(InvoiceItem $invoiceItem)
    {
        DB::transaction(function () use ($invoiceItem): void {
            $invoiceId = $invoiceItem->invoice_id;
            Invoice::query()->whereKey($invoiceId)->lockForUpdate()->firstOrFail()->assertItemsEditable();
            $invoiceItem->delete();
            Invoice::query()->findOrFail($invoiceId)->recalculateAmount();
        });

        return response()->json([
            'message' => 'Item de nota fiscal excluído com sucesso.',
        ], 200);
    }
}
