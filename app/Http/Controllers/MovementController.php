<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function __construct(private readonly StockLedger $stockLedger) {}

    public function entries(): View
    {
        return $this->page('Entrada', 'entrance.entrance', 'entrance');
    }

    public function balances(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'product_condition' => ['required', Rule::in(['Novo', 'Reforma', 'Descarte'])],
            'warehouse_ids' => ['required', 'array', 'min:1', 'max:2'],
            'warehouse_ids.*' => ['required', 'integer', 'distinct', 'exists:warehouses,warehouse_id'],
        ], [
            'product_id.required' => 'Selecione um produto para consultar o saldo.',
            'product_id.exists' => 'O produto selecionado não existe.',
            'product_condition.in' => 'Selecione uma condição válida para consultar o saldo.',
            'warehouse_ids.required' => 'Selecione um armazém para consultar o saldo.',
            'warehouse_ids.*.exists' => 'Um dos armazéns selecionados não existe.',
        ]);
        $warehouseIds = array_map('intval', $data['warehouse_ids']);
        $balances = DB::table('inventory')->where('product_id', $data['product_id'])
            ->where('product_condition', $data['product_condition'])
            ->whereIn('warehouse_id', $warehouseIds)->pluck('theoretical_balance', 'warehouse_id');

        return response()->json([
            'product_id' => (int) $data['product_id'],
            'product_condition' => $data['product_condition'],
            'balances' => array_map(fn (int $warehouseId): array => [
                'warehouse_id' => $warehouseId, 'balance' => (int) $balances->get($warehouseId, 0),
            ], $warehouseIds),
        ])->header('Cache-Control', 'no-store, private');
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
        throw ValidationException::withMessages([
            'invoice_id' => 'As saídas são registradas automaticamente ao importar uma NF-e de venda. Abra Consulta NF-e para concluir a operação.',
        ]);
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
            'invoiceOptions' => DB::table('invoices')
                ->orderByDesc('invoice_id')
                ->get(['invoice_id', 'invoice_number', 'invoice_serie'])
                ->mapWithKeys(fn ($invoice): array => [
                    (string) $invoice->invoice_id => $invoice->invoice_number.' / '.$invoice->invoice_serie,
                ])
                ->all(),
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
            'is_fiscal' => ['sometimes', 'boolean'],
            'invoice_id' => ['nullable', 'required_if:is_fiscal,1', 'integer', 'exists:invoices,invoice_id'],
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
            $isFiscal = filter_var($data['is_fiscal'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $invoiceId = $isFiscal ? (int) $data['invoice_id'] : null;

            Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();

            if ($direction === 'Entrada') {
                $this->stockLedger->change($productId, $destinationWarehouseId, $condition, $quantity);
            } elseif ($direction === 'Saída') {
                $this->stockLedger->change($productId, $originWarehouseId, $condition, -$quantity);
            } else {
                $this->stockLedger->change($productId, $originWarehouseId, $condition, -$quantity);
                $this->stockLedger->change($productId, $destinationWarehouseId, $condition, $quantity);
            }

            DB::table('stock_movements')->insert([
                'origin_warehouse_id' => $originWarehouseId,
                'dest_warehouse_id' => $destinationWarehouseId,
                'product_id' => $productId,
                'product_condition' => $condition,
                'user_id' => auth()->id(),
                'invoice_id' => $invoiceId,
                'quantity' => $quantity,
                'movement_type' => $direction,
                'movement_justification' => $data['movement_justification'] ?? null,
                'movement_date' => $data['date'],
                'is_fiscal' => $isFiscal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
