-- Relatório de vendas por produto para um intervalo de emissão inclusivo.
-- O arquivo pode ser executado no cliente MySQL; a migration Laravel extrai o CREATE PROCEDURE.
DROP PROCEDURE IF EXISTS sp_relatorio_vendas_periodo;

DELIMITER $$
CREATE PROCEDURE sp_relatorio_vendas_periodo(
    IN p_data_inicio DATE,
    IN p_data_fim DATE
)
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
    IF p_data_inicio IS NULL OR p_data_fim IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Informe as datas inicial e final do período.';
    END IF;

    IF p_data_inicio > p_data_fim THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A data inicial não pode ser posterior à data final.';
    END IF;

    SELECT
        product.product_id,
        product.sku,
        product.product_name,
        period_sales.invoice_count,
        period_sales.units_sold,
        period_sales.gross_revenue,
        COALESCE(stock.current_stock, 0) AS current_stock
    FROM products AS product
    INNER JOIN (
        SELECT
            invoice_item.product_id,
            COUNT(DISTINCT invoice.invoice_id) AS invoice_count,
            SUM(invoice_item.quantity) AS units_sold,
            SUM(invoice_item.quantity * invoice_item.unit_price) AS gross_revenue
        FROM invoice_items AS invoice_item
        INNER JOIN invoices AS invoice ON invoice.invoice_id = invoice_item.invoice_id
        WHERE invoice.issue_date >= p_data_inicio
            AND invoice.issue_date < DATE_ADD(p_data_fim, INTERVAL 1 DAY)
        GROUP BY invoice_item.product_id
        HAVING SUM(invoice_item.quantity) > 0
    ) AS period_sales ON period_sales.product_id = product.product_id
    LEFT JOIN (
        SELECT
            product_id,
            SUM(theoretical_balance) AS current_stock
        FROM inventory
        GROUP BY product_id
    ) AS stock ON stock.product_id = product.product_id
    ORDER BY
        period_sales.units_sold DESC,
        period_sales.gross_revenue DESC,
        product.product_name ASC;
END$$
DELIMITER ;

-- Exemplo: CALL sp_relatorio_vendas_periodo('2026-01-01', '2026-01-31');
