<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;

class InvoiceItemController extends Controller
{
    public function index()
    {
        $items = InvoiceItem::all();
        return response()->json($items);
    }

    public function create()
    {
        // return view('invoice_items.create');
    }

    public function store(StoreInvoiceItemRequest $request)
    {
        InvoiceItem::create($request->validated());
        return redirect()->route('itens-fatura.index');
    }

    public function show(InvoiceItem $invoiceItem)
    {
        // return view('invoice_items.show', compact('invoiceItem'));
    }

    public function edit(InvoiceItem $invoiceItem)
    {
        // return view('invoice_items.edit', compact('invoiceItem'));
    }

    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem)
    {
        $invoiceItem->update($request->validated());
        return redirect()->route('itens-fatura.index');
    }

    public function destroy(InvoiceItem $invoiceItem)
    {
        $invoiceItem->delete();
        return redirect()->route('itens-fatura.index');
    }
}
