<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoices;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoices::all();

        return InvoiceResource::collection($invoices);
    }

    public function create()
    {
        // return view('invoices.create');
    }

    public function store(StoreInvoiceRequest $request)
    {
        $dados = $request->validated();

        $invoice = Invoices::create($dados);

        return response()->json($invoice, 201);

    }

    public function show(string $id)
    {
        $invoice = Invoices::findOrFail($id);

        return new InvoiceResource($invoice);
    }

    public function edit(Invoices $invoice)
    {
        // return view('invoices.edit', compact('invoice'));
    }

    public function update(UpdateInvoiceRequest $request, string $id)
    {
        $invoice = Invoices::findOrFail($id);

        $dados = $request->validated();

        $invoice->update($dados);

        return new InvoiceResource($invoice->fresh());
    }

    public function destroy(Invoices $invoice, string $id)
    {
        $invoices = Invoices::findOrFail($id);
        $invoices->delete();

        return response()->json([
            'message' => 'Nota fiscal excluída com sucesso.',
        ], 200);
    }
}
