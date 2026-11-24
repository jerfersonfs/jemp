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
                $createdMovement = $this->stockLedger->record($data);
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
