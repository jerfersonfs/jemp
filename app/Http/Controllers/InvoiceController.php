<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\SalePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {

        return InvoiceResource::collection(Invoice::with(['items', 'payment'])->get());
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $invoice = DB::transaction(function () use ($data): Invoice {
            $invoice = Invoice::create(['customer_id' => $data['customer_id'], 'invoice_number' => $data['invoice_number'],
                'invoice_serie' => $data['invoice_serie'], 'issue_date' => $data['issue_date'], 'delivery_driver' => $data['delivery_driver'] ?? null, 'amount' => 0]);
            $products = Product::whereKey(array_column($data['items'], 'product_id'))->sharedLock()->get()->keyBy('product_id');
            $total = 0;
            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']) ?? throw ValidationException::withMessages(['items' => 'Produto indisponível.']);
                $price = (string) $product->cost_price;
                $total += SalePayment::cents($price) * (int) $item['quantity'];
                if ($total > 9999999999) {
                    throw ValidationException::withMessages(['items' => 'O valor total excede o limite da nota.']);
                }
                $invoice->items()->create(['product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_price' => $price]);
            }
            $invoice->amount = number_format($total / 100, 2, '.', '');
            $invoice->save();
            SalePayment::record($invoice, $data);

            return $invoice;
        });

        return response()->json(new InvoiceResource($invoice->load(['items', 'payment'])), 201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {

        return new InvoiceResource($invoice->refresh()->load(['items', 'payment']));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $data = $request->validated();
        if (isset($data['payment_status'])) {
            if ($data['payment_status'] === 'Paga') {
                SalePayment::receive($invoice, $data['payment_method'], Payment::today());
            } elseif ($invoice->is_cancelled || $invoice->payment?->paid_at !== null) {
                abort(409, 'Esta venda já está encerrada.');
            }
        } else {
            $invoice->update($data);
        }

        return new InvoiceResource($invoice->load('payment'));
    }

    public function settle(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['missing'], 'amount_due' => ['missing'], 'installment_number' => ['missing'],
            'paid_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.Payment::today()],
            'payment_method' => ['required', Rule::in(Payment::METHODS)],
        ]);

        $payment = SalePayment::receive($invoice, $data['payment_method'], $data['paid_at']);

        return response()->json(['message' => 'Recebimento registrado.', 'data' => $payment]);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        DB::transaction(function () use ($invoice): void {
            $locked = Invoice::query()->whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            if ($locked->payment()->exists() || $locked->access_key !== null || $locked->stockMovements()->exists()) {
                throw ValidationException::withMessages(['invoice_id' => 'Notas com pagamento registrado ou movimentação de estoque não podem ser excluídas.']);
            }
            $locked->delete();
        });

        return response()->json(['message' => 'Nota fiscal excluída com sucesso.']);
    }
}
