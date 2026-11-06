<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::with(['product', 'warehouse'])->get();

        return response()->json($inventories);
    }

    public function create()
    {
        // return view('inventories.create');
    }

    public function store(StoreInventoryRequest $request)
    {
        Inventory::create($request->validated());
        return redirect()->route('inventarios.index');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'warehouse']);

        return response()->json($inventory);
    }

    public function edit(Inventory $inventory)
    {
        // return view('inventories.edit', compact('inventory'));
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory)
    {
        $inventory->update($request->validated());
        return redirect()->route('inventarios.index');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventarios.index');
    }

    public function removeStock(Request $request)
    {
        $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
            'product_id'   => ['required', 'string'], // Ou integer, dependendo da tua tabela
            'quantity'     => ['required', 'numeric', 'min:0.01']
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $warehouseId = $request->input('warehouse_id');
                $productId   = $request->input('product_id');
                $quantityToRemove = $request->input('quantity');

                $inventory = Inventory::where('warehouse_id', $warehouseId)
                                      ->where('product_id', $productId)
                                      ->lockForUpdate()
                                      ->first();

                if (!$inventory) {
                    throw new \Exception('Produto não encontrado neste armazém. Saída cancelada.');
                }

                if ($inventory->theoretical_balance < $quantityToRemove) {
                    throw new \Exception(
                        "Estoque insuficiente. Saldo atual: {$inventory->theoretical_balance} | Solicitado: {$quantityToRemove}"
                    );
                }

                $inventory->decrement('theoretical_balance', $quantityToRemove);


                return $inventory;
            });

            return response()->json([
                'message' => 'Saída de estoque processada com sucesso!',
                'data'    => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Falha na movimentação de estoque.',
                'details' => $e->getMessage()
            ], 422);
        }
    }
}
