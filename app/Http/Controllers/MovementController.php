<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function entries(): View
    {
        return $this->page('Entrada', 'entrance.entrance', 'entrance');
    }

    public function exits(): View
    {
        return $this->page('Saída', 'exits.exits', 'exits');
    }

    public function transfers(): View
    {
        return $this->page('Transferência', 'internal.internal', 'internal');
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $data = $request->validate($this->commonRules() + [
            'dest_warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
        ]);
        $data['origin_warehouse_id'] = null;

        $this->createMovement($data, 'Entrada');

        return redirect()->route('entrance.index')->with('status', 'Entrada registrada com sucesso.');
    }

    public function storeExit(Request $request): RedirectResponse
    {
        $data = $request->validate($this->commonRules() + [
            'origin_warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
        ]);
        $data['dest_warehouse_id'] = null;

        $this->createMovement($data, 'Saída');

        return redirect()->route('exits.index')->with('status', 'Saída registrada com sucesso.');
    }

    public function storeTransfer(Request $request): RedirectResponse
    {
        $data = $request->validate($this->commonRules() + [
            'origin_warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id', 'different:dest_warehouse_id'],
            'dest_warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id', 'different:origin_warehouse_id'],
        ]);

        $this->createMovement($data, 'Transferência');

        return redirect()->route('internal.index')->with('status', 'Transferência registrada com sucesso.');
    }

    private function page(string $direction, string $view, string $page): View
    {
        $movements = DB::table('stock_movements as movement')
            ->join('products', 'products.product_id', '=', 'movement.product_id')
            ->leftJoin('warehouses as origin', 'origin.warehouse_id', '=', 'movement.origin_warehouse_id')
            ->leftJoin('warehouses as destination', 'destination.warehouse_id', '=', 'movement.dest_warehouse_id')
            ->where('movement.movement_type', $direction)
            ->orderByDesc('movement.movement_date')
            ->select([
                'movement.*',
                'products.sku as product_sku',
                'products.product_name',
                'origin.warehouse_name as origin_warehouse_name',
                'destination.warehouse_name as destination_warehouse_name',
            ])
            ->get();

        return view($view, [
            'page' => $page,
            'products' => Product::query()->orderBy('sku')->get(['product_id', 'sku', 'product_name']),
            'movements' => $movements,
            'locations' => Warehouse::query()->orderBy('warehouse_name')->get()->mapWithKeys(
                fn (Warehouse $warehouse): array => [(string) $warehouse->warehouse_id => $warehouse->warehouse_name],
            )->all(),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    private function commonRules(): array
    {
        return [
            'date' => ['required', 'date'],
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'product_condition' => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'movement_justification' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function createMovement(array $data, string $direction): void
    {
        DB::transaction(function () use ($data, $direction): void {
            $productId = (int) $data['product_id'];
            $quantity = (int) $data['quantity'];
            $condition = (string) $data['product_condition'];
            $originWarehouseId = isset($data['origin_warehouse_id']) ? (int) $data['origin_warehouse_id'] : null;
            $destinationWarehouseId = isset($data['dest_warehouse_id']) ? (int) $data['dest_warehouse_id'] : null;

            Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();

            if ($direction === 'Entrada') {
                $this->changeInventory($productId, $destinationWarehouseId, $condition, $quantity);
            } elseif ($direction === 'Saída') {
                $this->changeInventory($productId, $originWarehouseId, $condition, -$quantity);
            } else {
                $this->changeInventory($productId, $originWarehouseId, $condition, -$quantity);
                $this->changeInventory($productId, $destinationWarehouseId, $condition, $quantity);
            }

            DB::table('stock_movements')->insert([
                'origin_warehouse_id' => $originWarehouseId,
                'dest_warehouse_id' => $destinationWarehouseId,
                'product_id' => $productId,
                'product_condition' => $condition,
                'user_id' => auth()->id(),
                'invoice_id' => null,
                'quantity' => $quantity,
                'movement_type' => $direction,
                'movement_justification' => $data['movement_justification'] ?? null,
                'movement_date' => $data['date'],
                'is_fiscal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function changeInventory(int $productId, ?int $warehouseId, string $condition, int $change): void
    {
        if ($warehouseId === null) {
            throw ValidationException::withMessages(['warehouse_id' => 'Selecione um armazém.']);
        }

        $inventory = DB::table('inventory')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_condition', $condition)
            ->lockForUpdate()
            ->first();

        if ($inventory === null && $change < 0) {
            throw ValidationException::withMessages(['quantity' => 'Não há saldo deste produto e condição no armazém selecionado.']);
        }

        if ($inventory === null) {
            DB::table('inventory')->insert([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'product_condition' => $condition,
                'theoretical_balance' => $change,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        $newBalance = (int) $inventory->theoretical_balance + $change;
        if ($newBalance < 0) {
            throw ValidationException::withMessages(['quantity' => 'A quantidade informada excede o saldo disponível no armazém.']);
        }

        DB::table('inventory')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_condition', $condition)
            ->update(['theoretical_balance' => $newBalance, 'updated_at' => now()]);
    }
}
