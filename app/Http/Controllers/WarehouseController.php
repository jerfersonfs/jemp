<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('inventories.product')->get();

        return response()->json($warehouses);
    }

    public function create()
    {
        // return view('warehouses.create');
    }

    public function store(StoreWarehouseRequest $request)
    {
        Warehouse::create($request->validated());
        return redirect()->route('armazens.index');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('inventories.product');

        return response()->json($warehouse);
    }

    public function edit(Warehouse $warehouse)
    {
        // return view('warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());
        return redirect()->route('armazens.index');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('armazens.index');
    }
}
