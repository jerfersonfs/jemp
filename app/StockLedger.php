<?php

namespace App;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockLedger
{
    /** @param array<string, mixed> $data */
    public function record(array $data): StockMovement
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('A movimentação de estoque exige uma transação.');
        }

        $product = Product::query()->whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
        $quantity = (int) $data['quantity'];
        $origin = isset($data['origin_warehouse_id']) ? (int) $data['origin_warehouse_id'] : null;
        $destination = isset($data['dest_warehouse_id']) ? (int) $data['dest_warehouse_id'] : null;
        $validWarehouses = match ($data['movement_type']) {
            'Entrada' => $origin === null && $destination !== null,
            'Saída' => $origin !== null && $destination === null,
            'Transferência' => $origin !== null && $destination !== null && $origin !== $destination,
            default => false,
        };

        if (! $validWarehouses || $quantity < 1 || $quantity > 2147483647) {
            throw ValidationException::withMessages(['movement_type' => 'Confira a quantidade, origem e destino da movimentação.']);
        }

        Warehouse::lockActive(['origin_warehouse_id' => $origin, 'dest_warehouse_id' => $destination]);

        if ($data['movement_type'] === 'Saída' && ! DB::table('stock_condition_settings')
            ->where('product_condition', $data['product_condition'])->sharedLock()->value('saleable')) {
            throw ValidationException::withMessages(['product_condition' => 'Esta condição está desabilitada para venda na Parametrização.']);
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $migrations = config('database.migrations.table');
            if (! DB::table($migrations)->where('migration', '2026_11_21_182508_create_stock_movement_inventory_triggers')->exists()) {
                throw ValidationException::withMessages(['movement_type' => 'A atualização do banco para movimentações ainda está pendente.']);
            }
        } else {
            if ($origin !== null) {
                $this->change($product, $origin, $data['product_condition'], -$quantity);
            }

            if ($destination !== null) {
                $this->change($product, $destination, $data['product_condition'], $quantity);
            }
        }

        try {
            return StockMovement::query()->create($data);
        } catch (QueryException $exception) {
            $number = (int) ($exception->errorInfo[1] ?? 0);
            $message = match ($number) {
                30101 => 'Confira a quantidade, origem e destino da movimentação.',
                30102 => "Saldo insuficiente de {$product->sku} na origem selecionada. Nenhum item foi baixado.",
                30103 => 'O saldo resultante excede o limite do estoque.',
                default => null,
            };

            if ($message === null || ($exception->errorInfo[0] ?? null) !== '45000') {
                throw $exception;
            }

            throw ValidationException::withMessages(['quantity' => $message]);
        }
    }

    private function change(Product $product, int $warehouseId, string $condition, int $change): void
    {
        $productId = $product->getKey();
        $query = DB::table('inventory')->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)->where('product_condition', $condition);
        $inventory = (clone $query)->lockForUpdate()->first();
        $balance = (int) ($inventory?->theoretical_balance ?? 0);

        if ($balance + $change < 0) {
            throw ValidationException::withMessages([
                'quantity' => "Saldo insuficiente de {$product->sku} ({$condition}): disponível {$balance}, solicitado ".abs($change).'. Nenhum item foi baixado.',
            ]);
        }

        if ($balance + $change > 2147483647) {
            throw ValidationException::withMessages(['quantity' => 'O saldo resultante excede o limite do estoque.']);
        }

        if ($inventory === null) {
            DB::table('inventory')->insert([
                'product_id' => $productId, 'warehouse_id' => $warehouseId,
                'product_condition' => $condition, 'theoretical_balance' => $change,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        } else {
            $query->update(['theoretical_balance' => $balance + $change, 'updated_at' => now()]);
        }
    }
}
