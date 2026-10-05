<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::all();
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
        // return view('inventories.show', compact('inventory'));
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
}
