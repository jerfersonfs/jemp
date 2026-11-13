<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockLedger $stockLedger) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockMovements = StockMovement::with([
            'product',
            'originWarehouse',
            'destinationWarehouse',
            'user',
            'invoice',
        ])->get();

        return response()->json($stockMovements);
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

        $data = $request->validated();

        try {
            $movement = DB::transaction(function () use ($data) {
                $type = $data['movement_type'];
                $qty = $data['quantity'];
                $productId = $data['product_id'];
                $destWarehouseId = $data['dest_warehouse_id'] ?? null;
                $originWarehouseId = $data['origin_warehouse_id'] ?? null;
                $condition = $data['product_condition'];

                if ($type === 'Transferência') {
                    $this->stockLedger->change((int) $productId, (int) $originWarehouseId, $condition, -(int) $qty);
                }
                if ($type === 'Entrada' || $type === 'Transferência') {
                    $this->stockLedger->change((int) $productId, (int) $destWarehouseId, $condition, (int) $qty);
                }
                $createdMovement = StockMovement::create($data);
                $createdMovement->load([
                    'product', 'originWarehouse', 'destinationWarehouse', 'user', 'invoice',
                ]);

                return $createdMovement;
            });

            return response()->json([
                'message' => 'Movimentação registrada e saldo atualizado com sucesso.',
                'data' => $movement,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Falha ao processar movimentação.',
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load([
            'product',
            'originWarehouse',
            'destinationWarehouse',
            'user',
            'invoice',
        ]);

        return response()->json($stockMovement);
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
