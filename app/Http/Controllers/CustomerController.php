<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Lista todos os clientes
     */
    public function index()
    {
        $customers = Customer::with('invoices')->get();

        return response()->json($customers);
    }

    /**
     * Guarda um novo cliente na base de dados
     */
    public function store(Request $request)
    {
        // Nota de arquitetura: O ideal para a Sprint 4 é criar um StoreCustomerRequest
        // para validar dados como CPF/NIF, email, etc., tal como fez nos Produtos.
        // Por agora, o fillable do Model irá proteger a inserção básica.
        $customer = Customer::create($request->all());

        return response()->json([
            'message' => 'Cliente criado com sucesso.',
            'data' => $customer
        ], 201);
    }

    /**
     * Mostra os detalhes de um cliente específico
     */
    public function show(Customer $customer)
    {
        $customer->load('invoices');

        return response()->json($customer);
    }

    /**
     * Atualiza um cliente existente
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all());

        return response()->json([
            'message' => 'Cliente atualizado com sucesso.',
            'data' => $customer
        ], 200);
    }

    /**
     * Remove o cliente da base de dados
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'message' => 'Cliente excluído com sucesso.'
        ], 200);
    }
}
