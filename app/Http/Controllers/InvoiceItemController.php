<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;

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
        try {
            $invoiceItem = InvoiceItem::create($request->validated());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao cadastrar item da nota fiscal.'], 500);
        }

        // Carrega os relacionamentos para devolver o objeto completo ao frontend
        $invoiceItem->load(['invoice', 'product']);

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
        try {
            $invoiceItem->update($request->validated());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao atualizar item da nota fiscal.'], 500);
        }

        $invoiceItem->load(['invoice', 'product']);

        return response()->json([
            'message' => 'Item de nota fiscal atualizado com sucesso.',
            'data' => $invoiceItem,
        ], 200);
    }

    /**
     * Remove um item de fatura
     */
    public function destroy(InvoiceItem $invoiceItem)
    {
        try {
            $invoiceItem->delete();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao excluir item da nota fiscal.'], 500);
        }

        return response()->json([
            'message' => 'Item de nota fiscal excluído com sucesso.',
        ], 200);
    }
}
