<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Lista todas as notas fiscais
     */
    public function index()
    {
        $invoices = Invoice::all();

        return InvoiceResource::collection($invoices);
    }

    /**
     * Guarda uma nova nota fiscal
     */
    public function store(StoreInvoiceRequest $request)
    {
        $dados = $request->validated();

        $invoice = Invoice::create($dados);

        return response()->json($invoice, 201);
    }

    /**
     * Mostra os detalhes de uma nota fiscal específica
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }

    /**
     * Atualiza uma nota fiscal existente
     */
    public function update(UpdateInvoiceRequest $request, Invoices $invoice)
    {
        $dados = $request->validated();

        $invoice->update($dados);

        // O fresh() garante que devolvemos os dados mais atualizados diretamente do banco
        return new InvoiceResource($invoice->fresh());
    }

    /**
     * Remove uma nota fiscal
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json([
            'message' => 'Nota fiscal excluída com sucesso.',
        ], 200);
    }
}
