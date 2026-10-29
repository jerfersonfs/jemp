<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Requests\UpdateStockMovementRequest;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::all();
        return response()->json($movements);
    }

    public function create()
    {
        // return view('movements.create');
    }

    public function store(StoreStockMovementRequest $request)
    {
        StockMovement::create($request->validated());
        return redirect()->route('movimentacoes.index');
    }

    public function show(StockMovement $movement)
    {
        // return view('movements.show', compact('movement'));
    }

    public function edit(StockMovement $movement)
    {
        // return view('movements.edit', compact('movement'));
    }

    public function update(UpdateStockMovementRequest $request, StockMovement $movement)
    {
        $movement->update($request->validated());
        return redirect()->route('movimentacoes.index');
    }

    public function destroy(StockMovement $movement)
    {
        $movement->delete();
        return redirect()->route('movimentacoes.index');
    }
}
