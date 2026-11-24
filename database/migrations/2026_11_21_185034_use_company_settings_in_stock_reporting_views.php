<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceStockViews('(SELECT low_stock_threshold FROM company_settings WHERE id = 1)');
    }

    public function down(): void
    {
        $this->replaceStockViews('20');
    }

    private function replaceStockViews(string $threshold): void
    {
        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW vw_estoque_atual AS
            SELECT e.*, 'un.' AS unit,
                CASE
                    WHEN e.theoretical_balance = 0 THEN 'Zerado'
                    WHEN e.theoretical_balance <= {$threshold} THEN 'Estoque baixo'
                    ELSE 'Normal'
                END AS stock_status
            FROM vw_estoque AS e
            SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW vw_estoque_baixo AS
            SELECT warehouse_id, warehouse_name, product_id, sku, product_name,
                category, material, product_condition, theoretical_balance
            FROM vw_estoque_atual
            WHERE stock_status IN ('Estoque baixo', 'Zerado')
            SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW vw_visao_geral AS
            SELECT
                (SELECT COUNT(*) FROM products) AS product_count,
                (SELECT COALESCE(SUM(theoretical_balance), 0) FROM inventory) AS total_stock,
                (SELECT COUNT(DISTINCT product_id) FROM vw_estoque_baixo) AS low_stock_products,
                (
                    SELECT COUNT(*) FROM stock_movements
                    WHERE movement_date >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
                        AND movement_date < DATE_ADD(LAST_DAY(CURRENT_DATE), INTERVAL 1 DAY)
                ) AS movements_in_period,
                (SELECT COUNT(*) FROM invoices) AS invoice_count
            SQL);
    }
};
