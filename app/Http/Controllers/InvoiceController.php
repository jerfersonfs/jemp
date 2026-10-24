<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;

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

    public function store(Request $request)
    {
        $dados = $request->validate([
        'customer_id' => ['required', 'exists:customers,customer_id'],
        'invoice_number' => ['required', 'integer', 'min:1'],
        'invoice_serie' => ['required', 'integer', 'min:1'],
        'issue_date' => ['required', 'date'],
        'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
        'payment_method' => ['required', 'string', 'max:50'],
        'delivery_driver' => ['nullable', 'string', 'max:100'],
        'payment_terms_days' => ['required', 'integer', 'min:0'],
        'payment_status' => ['sometimes', 'in:A vencer,Vencida,Paga,Cancelada'],
        'payment_date' => ['nullable', 'required_if:payment_status,Paga', 'date'],
        // 'amount' => $request->amount
    ]);

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

    public function update(UpdateInvoiceRequest $request, Invoices $invoice)
    {
        $invoice->update($request->validated());
        return redirect()->route('faturas.index');
    }

    public function destroy(Invoices $invoice)
    {
        $invoice->delete();
        return redirect()->route('faturas.index');
    }
}
