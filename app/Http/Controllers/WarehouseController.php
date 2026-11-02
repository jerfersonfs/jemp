<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;

class WarehouseController extends Controller
{
    /**
     * Lista todos os armazéns
     */
    public function index()
    {
        $warehouses = Warehouse::with('inventories.product')->get();

        return response()->json($warehouses);
    }

    /**
     * Guarda um novo armazém
     */
    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());

        return response()->json([
            'message' => 'Armazém criado com sucesso.',
            'data' => $warehouse
        ], 201);
    }

    /**
     * Mostra os detalhes de um armazém específico
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->load('inventories.product');

        return response()->json($warehouse);
    }

    /**
     * Atualiza um armazém existente
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return response()->json([
            'message' => 'Armazém atualizado com sucesso.',
            'data' => $warehouse
        ], 200);
    }

    /**
     * Remove um armazém
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json([
            'message' => 'Armazém excluído com sucesso.'
        ], 200);
    }
}
