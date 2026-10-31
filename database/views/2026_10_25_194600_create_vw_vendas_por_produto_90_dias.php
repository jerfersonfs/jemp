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
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW vw_vendas_por_produto_90_dias AS
            SELECT
                p.product_id,
                p.sku,
                p.product_name,
                p.category,
                COALESCE(sales.units_sold_last_90_days, 0) AS units_sold_last_90_days,
                COALESCE(sales.revenue_last_90_days, 0) AS revenue_last_90_days,
                COALESCE(stock.current_stock, 0) AS current_stock,
                (
                    SELECT MAX(last_invoice.issue_date)
                    FROM invoice_items AS last_item
                    INNER JOIN invoices AS last_invoice ON last_invoice.invoice_id = last_item.invoice_id
                    WHERE last_item.product_id = p.product_id
                ) AS last_sale_date
            FROM products AS p
            LEFT JOIN (
                SELECT
                    invoice_item.product_id,
                    SUM(invoice_item.quantity) AS units_sold_last_90_days,
                    SUM(invoice_item.quantity * invoice_item.unit_price) AS revenue_last_90_days
                FROM invoice_items AS invoice_item
                INNER JOIN invoices AS invoice ON invoice.invoice_id = invoice_item.invoice_id
                WHERE invoice.issue_date >= DATE_SUB(CURRENT_DATE, INTERVAL 89 DAY)
                    AND invoice.issue_date < DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY)
                GROUP BY invoice_item.product_id
            ) AS sales ON sales.product_id = p.product_id
            LEFT JOIN (
                SELECT
                    product_id,
                    SUM(theoretical_balance) AS current_stock
                FROM inventory
                GROUP BY product_id
            ) AS stock ON stock.product_id = p.product_id
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_vendas_por_produto_90_dias');
    }
};
