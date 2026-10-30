<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['customer', 'items.product'])->get();

        return response()->json($invoices);
    }

    public function create()
    {
        // return view('invoices.create');
    }

    public function store(StoreInvoiceRequest $request)
    {
        Invoice::create($request->validated());
        return redirect()->route('faturas.index');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'stockMovements.product']);

        return response()->json($invoice);
    }

    public function edit(Invoice $invoice)
    {
        // return view('invoices.edit', compact('invoice'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice->update($request->validated());
        return redirect()->route('faturas.index');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('faturas.index');
    }
}
