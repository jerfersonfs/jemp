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
            'customers' => Customer::query()->orderBy('name')->get(),
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
        $cliente->delete();

        return redirect()->route('customers.index')->with('status', 'Cliente excluído com sucesso.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Customer $cliente = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:30', Rule::unique('customers', 'document')->ignore($cliente)],
            'city' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['Ativo', 'Inativo'])],
        ];
    }
}
