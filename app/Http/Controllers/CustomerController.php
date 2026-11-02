<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $customers = Customer::query()->with('invoices')->orderBy('customer_name')->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $customers]);
        }

        return view('customers.customers', [
            'page' => 'customers',
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $customer = Customer::query()->create($request->validate($this->rules()));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Cliente criado com sucesso.', 'data' => $customer], 201);
        }

        return redirect()->route('customers.index')->with('status', 'Cliente cadastrado com sucesso.');
    }

    public function update(Request $request, Customer $cliente): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $cliente->update($request->validate($this->rules($cliente)));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Cliente atualizado com sucesso.', 'data' => $cliente->load('invoices')]);
        }

        return redirect()->route('customers.index')->with('status', 'Cliente atualizado com sucesso.');
    }

    public function show(Customer $cliente): \Illuminate\Http\JsonResponse
    {
        return response()->json($cliente->load('invoices'));
    }

    public function destroy(Customer $cliente): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($cliente->invoices()->exists()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Clientes vinculados a notas fiscais não podem ser excluídos.'], 409);
            }

            return redirect()->route('customers.index')->with('error', 'Clientes vinculados a notas fiscais não podem ser excluídos.');
        }

        $cliente->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Cliente excluído com sucesso.']);
        }

        return redirect()->route('customers.index')->with('status', 'Cliente excluído com sucesso.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Customer $cliente = null): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_document' => ['required', 'string', 'max:20', Rule::unique('customers', 'customer_document')->ignore($cliente?->customer_id, 'customer_id')],
            'customer_contact' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_payment_terms' => ['required', 'string', 'max:255'],
        ];
    }
}
