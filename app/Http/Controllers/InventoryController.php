<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        try {
            $data = $request->validated();
            $inventory = DB::transaction(function () use ($data): Inventory {
                Warehouse::lockActive(['warehouse_id' => $data['warehouse_id']]);

                return Inventory::create($data)->load(['product', 'warehouse']);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao cadastrar inventario.'], 500);
        }

        return response()->json([
            'message' => 'Inventário cadastrado com sucesso.',
            'data' => $inventory,
        ], 201);
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
        try {
            $data = $request->validated();
            DB::transaction(function () use ($inventory, $data): void {
                Warehouse::lockActive(['inventory' => $inventory->warehouse_id, 'warehouse_id' => $data['warehouse_id'] ?? null]);
                $inventory->update($data);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao atualizar inventario.'], 500);
        }

        return response()->json([
            'message' => 'Inventário atualizado com sucesso.',
            'data' => $inventory->load(['product', 'warehouse']),
        ]);
    }

    public function destroy(Inventory $inventory)
    {
        try {
            DB::transaction(function () use ($inventory): void {
                Warehouse::lockActive(['warehouse_id' => $inventory->warehouse_id]);
                $inventory->delete();
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao excluir inventario.'], 500);
        }

        return response()->noContent();
    }

    public function removeStock(Request $request)
    {
        $request->validate([
            'warehouse_id' => ['required', 'integer', Warehouse::activeIdRule()],
            'product_id' => ['required', 'string'], // Ou integer, dependendo da tua tabela
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $warehouseId = $request->input('warehouse_id');
                Warehouse::lockActive(['warehouse_id' => $warehouseId]);
                $productId = $request->input('product_id');
                $quantityToRemove = $request->input('quantity');

                $inventory = Inventory::where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
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
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Falha na movimentação de estoque.',
                'details' => $e->getMessage(),
            ], 422);
        }
    }
}
