<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.customers', [
            'page' => 'customers',
            'customers' => Customer::query()->orderBy('customer_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::query()->create($request->validate($this->rules()));

        return redirect()->route('customers.index')->with('status', 'Cliente cadastrado com sucesso.');
    }

    public function update(Request $request, Customer $cliente): RedirectResponse
    {
        $cliente->update($request->validate($this->rules($cliente)));

        return redirect()->route('customers.index')->with('status', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Customer $cliente): RedirectResponse
    {
        if ($cliente->invoices()->exists()) {
            return redirect()->route('customers.index')->with('error', 'Clientes vinculados a notas fiscais não podem ser excluídos.');
        }

        $cliente->delete();

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
