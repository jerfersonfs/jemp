<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
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
        // return view('invoices.show', compact('invoice'));
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
