<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceView("WHEN NOT EXISTS (SELECT 1 FROM stock_condition_settings s WHERE s.product_condition = e.product_condition AND s.saleable = 1) THEN 'Fora de venda'");
    }

    public function down(): void
    {
        $this->replaceView('');
    }

    private function replaceView(string $saleableRule): void
    {
        $create = 'CREATE OR REPLACE VIEW';
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP VIEW IF EXISTS vw_estoque_atual');
            $create = 'CREATE VIEW';
        }
        DB::statement(<<<SQL
            {$create} vw_estoque_atual AS
            SELECT e.*, 'un.' AS unit,
                CASE
                    {$saleableRule}
                    WHEN e.theoretical_balance = 0 THEN 'Zerado'
                    WHEN e.theoretical_balance <= (SELECT low_stock_threshold FROM company_settings WHERE id = 1) THEN 'Estoque baixo'
                    ELSE 'Normal'
                END AS stock_status
            FROM vw_estoque e
            SQL);
    }
};
