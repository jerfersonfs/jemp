<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockMovements = StockMovement::all();
        return StockMovementResource::collection($stockMovements);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockMovementRequest $request)
    {
        // O Request já validou se os tipos estão corretos e se a quantidade é > 0
        $data = $request->validated();

        // Iniciamos a Transação. Se qualquer erro acontecer aqui dentro, o Laravel desfaz tudo no banco (Rollbac          k automático).
        $result = DB::transaction(function () use ($data) {
            $type = $data['movement_type'];
            $qty = $data['quantity'];
            $productId = $data['product_id'];
            $destWarehouseId = $data['dest_warehouse_id'] ?? null;
            $originWarehouseId = $data['origin_warehouse_id'] ?? null;
            $condition = $data['product_condition'];

            // Lógica de Entrada ou Transferência (Destino)
            if ($type === 'Entrada' || $type === 'Transferência') {
                $inventoryDest = Inventory::firstOrCreate(
                    ['warehouse_id' => $destWarehouseId, 'product_id' => $productId, 'product_condition' => $condition],
                    ['theoretical_balance' => 0]
                );

                // ATUALIZAÇÃO EXPLÍCITA: Encontra a linha pelas suas 3 chaves e soma
                Inventory::where('warehouse_id', $destWarehouseId)
                         ->where('product_id', $productId)
                         ->where('product_condition', $condition)
                         ->update(['theoretical_balance' => $inventoryDest->theoretical_balance + $qty]);
            }

            // Lógica de Saída ou Transferência (Origem)
            if ($type === 'Saída' || $type === 'Transferência') {
                $inventoryOrigin = Inventory::where('warehouse_id', $originWarehouseId)
                    ->where('product_id', $productId)
                    ->where('product_condition', $condition)
                    ->first();

                if (!$inventoryOrigin || $inventoryOrigin->theoretical_balance < $qty) {
                    abort(response()->json(['error' => 'Saldo insuficiente.'], 422));
                }

                // ATUALIZAÇÃO EXPLÍCITA: Encontra a linha pelas suas 3 chaves e subtrai
                Inventory::where('warehouse_id', $originWarehouseId)
                         ->where('product_id', $productId)
                         ->where('product_condition', $condition)
                         ->update(['theoretical_balance' => $inventoryOrigin->theoretical_balance - $qty]);
            }

            // Agora sim, grava o recibo da movimentação
            $movement = StockMovement::create($data);

            return response()->json([
                'message' => 'Movimentação registrada e saldo atualizado com sucesso.',
                'data' => $movement
            ], 201);
        });
        return $result;
    }
    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockMovement $stockMovement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockMovement $stockMovement)
    {
        //
    }
}
