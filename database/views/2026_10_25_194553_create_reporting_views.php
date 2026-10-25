<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $views = [
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_estoque AS
                SELECT
                    w.warehouse_id,
                    w.warehouse_name,
                    p.product_id,
                    p.sku,
                    p.product_name,
                    p.category,
                    p.material,
                    p.length_mm,
                    p.width_mm,
                    i.product_condition,
                    i.theoretical_balance
                FROM inventory AS i
                INNER JOIN warehouses AS w ON w.warehouse_id = i.warehouse_id
                INNER JOIN products AS p ON p.product_id = i.product_id
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_estoque_baixo AS
                SELECT
                    w.warehouse_id,
                    w.warehouse_name,
                    p.product_id,
                    p.sku,
                    p.product_name,
                    p.category,
                    p.material,
                    i.product_condition,
                    i.theoretical_balance
                FROM inventory AS i
                INNER JOIN warehouses AS w ON w.warehouse_id = i.warehouse_id
                INNER JOIN products AS p ON p.product_id = i.product_id
                WHERE i.theoretical_balance <= 20
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_historico_movimentacoes AS
                SELECT
                    sm.sm_id,
                    sm.movement_date,
                    sm.movement_type,
                    sm.product_id,
                    p.sku,
                    p.product_name,
                    sm.product_condition,
                    sm.quantity,
                    sm.origin_warehouse_id,
                    wo.warehouse_name AS origin_warehouse_name,
                    sm.dest_warehouse_id,
                    wd.warehouse_name AS destination_warehouse_name,
                    sm.user_id,
                    u.username,
                    sm.invoice_id,
                    sm.is_fiscal,
                    sm.movement_justification
                FROM stock_movements AS sm
                INNER JOIN products AS p ON p.product_id = sm.product_id
                INNER JOIN users AS u ON u.user_id = sm.user_id
                LEFT JOIN warehouses AS wo ON wo.warehouse_id = sm.origin_warehouse_id
                LEFT JOIN warehouses AS wd ON wd.warehouse_id = sm.dest_warehouse_id
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_notas_fiscais AS
                SELECT
                    i.invoice_id,
                    i.invoice_number,
                    i.invoice_serie,
                    c.customer_id,
                    c.customer_name,
                    c.customer_document,
                    i.issue_date,
                    i.due_date,
                    i.payment_method,
                    i.payment_status,
                    i.payment_date,
                    COALESCE(SUM(ii.quantity * ii.unit_price), 0) AS total_value
                FROM invoices AS i
                INNER JOIN customers AS c ON c.customer_id = i.customer_id
                LEFT JOIN invoice_items AS ii ON ii.invoice_id = i.invoice_id
                GROUP BY
                    i.invoice_id,
                    i.invoice_number,
                    i.invoice_serie,
                    c.customer_id,
                    c.customer_name,
                    c.customer_document,
                    i.issue_date,
                    i.due_date,
                    i.payment_method,
                    i.payment_status,
                    i.payment_date
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_notas_vencidas AS
                SELECT
                    invoice_id,
                    invoice_number,
                    invoice_serie,
                    customer_id,
                    customer_name,
                    customer_document,
                    issue_date,
                    due_date,
                    payment_method,
                    payment_status,
                    payment_date,
                    total_value
                FROM vw_notas_fiscais
                WHERE payment_status = 'Vencida'
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_estoque_atual AS
                SELECT
                    w.warehouse_id,
                    w.warehouse_name,
                    p.product_id,
                    p.sku,
                    p.product_name,
                    p.category,
                    p.material,
                    p.length_mm,
                    p.width_mm,
                    i.product_condition,
                    i.theoretical_balance,
                    'un.' AS unit,
                    CASE
                        WHEN i.theoretical_balance = 0 THEN 'Zerado'
                        WHEN i.theoretical_balance <= 20 THEN 'Estoque baixo'
                        ELSE 'Normal'
                    END AS stock_status
                FROM inventory AS i
                INNER JOIN warehouses AS w ON w.warehouse_id = i.warehouse_id
                INNER JOIN products AS p ON p.product_id = i.product_id
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_visao_geral AS
                SELECT
                    (SELECT COUNT(*) FROM products) AS product_count,
                    (SELECT COALESCE(SUM(theoretical_balance), 0) FROM inventory) AS total_stock,
                    (
                        SELECT COUNT(DISTINCT product_id)
                        FROM inventory
                        WHERE theoretical_balance <= 20
                    ) AS low_stock_products,
                    (
                        SELECT COUNT(*)
                        FROM stock_movements
                        WHERE movement_date >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
                            AND movement_date < DATE_ADD(LAST_DAY(CURRENT_DATE), INTERVAL 1 DAY)
                    ) AS movements_in_period,
                    (SELECT COUNT(*) FROM invoices) AS invoice_count
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_movimentacoes AS
                SELECT
                    sm.sm_id,
                    sm.movement_date,
                    sm.movement_type,
                    sm.product_id,
                    p.sku,
                    p.product_name,
                    sm.product_condition,
                    sm.quantity,
                    sm.origin_warehouse_id,
                    wo.warehouse_name AS origin_warehouse_name,
                    sm.dest_warehouse_id,
                    wd.warehouse_name AS destination_warehouse_name,
                    sm.user_id,
                    u.username,
                    sm.invoice_id,
                    CASE
                        WHEN i.invoice_id IS NULL THEN NULL
                        ELSE CONCAT(i.invoice_number, '/', i.invoice_serie)
                    END AS associated_document,
                    sm.is_fiscal,
                    sm.movement_justification
                FROM stock_movements AS sm
                INNER JOIN products AS p ON p.product_id = sm.product_id
                INNER JOIN users AS u ON u.user_id = sm.user_id
                LEFT JOIN warehouses AS wo ON wo.warehouse_id = sm.origin_warehouse_id
                LEFT JOIN warehouses AS wd ON wd.warehouse_id = sm.dest_warehouse_id
                LEFT JOIN invoices AS i ON i.invoice_id = sm.invoice_id
                SQL,
            <<<'SQL'
                CREATE OR REPLACE VIEW vw_auditoria AS
                SELECT
                    sm.sm_id AS record_id,
                    sm.user_id,
                    sm.movement_type,
                    u.username,
                    CASE sm.movement_type
                        WHEN 'Entrada' THEN 'Entrada registrada'
                        WHEN 'Saída' THEN 'Saída registrada'
                        WHEN 'Transferência' THEN 'Transferência registrada'
                        ELSE CONCAT(sm.movement_type, ' registrada')
                    END AS action,
                    'Estoque' AS module,
                    sm.movement_date AS occurred_at,
                    CONCAT(
                        sm.movement_type,
                        ' de ',
                        sm.quantity,
                        ' unidades de ',
                        p.product_name,
                        ' (',
                        sm.product_condition,
                        '); origem: ',
                        COALESCE(wo.warehouse_name, 'Externo'),
                        '; destino: ',
                        COALESCE(wd.warehouse_name, 'Externo'),
                        '; justificativa: ',
                        COALESCE(sm.movement_justification, '—')
                    ) AS description
                FROM stock_movements AS sm
                INNER JOIN products AS p ON p.product_id = sm.product_id
                INNER JOIN users AS u ON u.user_id = sm.user_id
                LEFT JOIN warehouses AS wo ON wo.warehouse_id = sm.origin_warehouse_id
                LEFT JOIN warehouses AS wd ON wd.warehouse_id = sm.dest_warehouse_id
                SQL,
        ];

        foreach ($views as $view) {
            DB::statement($view);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ([
            'vw_auditoria',
            'vw_movimentacoes',
            'vw_visao_geral',
            'vw_estoque_atual',
            'vw_notas_vencidas',
            'vw_notas_fiscais',
            'vw_historico_movimentacoes',
            'vw_estoque_baixo',
            'vw_estoque',
        ] as $view) {
            DB::statement('DROP VIEW IF EXISTS '.$view);
        }
    }
};
