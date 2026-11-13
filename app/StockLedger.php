<?php

namespace App;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockLedger
{
    public function change(int $productId, ?int $warehouseId, string $condition, int $change): void
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('A movimentação de estoque exige uma transação.');
        }

        if ($warehouseId === null) {
            throw ValidationException::withMessages(['warehouse_id' => 'Selecione um armazém.']);
        }

        $product = Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
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
