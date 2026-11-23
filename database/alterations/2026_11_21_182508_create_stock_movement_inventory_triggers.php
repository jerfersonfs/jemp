<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        // MySQL commits DDL independently. Allow retry after a partially applied migration.
        $this->down();

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_prepara_estoque_antes_movimento
            BEFORE INSERT ON stock_movements
            FOR EACH ROW
            BEGIN
                DECLARE origin_balance INT DEFAULT NULL;

                IF NEW.quantity <= 0 OR NOT (
                    (NEW.movement_type = 'Entrada' AND NEW.origin_warehouse_id IS NULL AND NEW.dest_warehouse_id IS NOT NULL)
                    OR (NEW.movement_type = 'Saída' AND NEW.origin_warehouse_id IS NOT NULL AND NEW.dest_warehouse_id IS NULL)
                    OR (NEW.movement_type = 'Transferência' AND NEW.origin_warehouse_id IS NOT NULL
                        AND NEW.dest_warehouse_id IS NOT NULL AND NEW.origin_warehouse_id <> NEW.dest_warehouse_id)
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 30101, MESSAGE_TEXT = 'Movimentação inválida.';
                END IF;

                IF NEW.origin_warehouse_id IS NOT NULL THEN
                    SELECT theoretical_balance INTO origin_balance
                    FROM inventory
                    WHERE warehouse_id = NEW.origin_warehouse_id
                        AND product_id = NEW.product_id
                        AND product_condition = NEW.product_condition
                    FOR UPDATE;

                    IF origin_balance IS NULL OR origin_balance < NEW.quantity THEN
                        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 30102, MESSAGE_TEXT = 'Saldo insuficiente na origem.';
                    END IF;
                END IF;

                IF NEW.dest_warehouse_id IS NOT NULL THEN
                    INSERT INTO inventory (warehouse_id, product_id, product_condition, theoretical_balance, created_at, updated_at)
                    VALUES (NEW.dest_warehouse_id, NEW.product_id, NEW.product_condition, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE theoretical_balance = theoretical_balance;
                END IF;
            END
            SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_atualiza_estoque_apos_movimento
            AFTER INSERT ON stock_movements
            FOR EACH ROW
            BEGIN
                IF NEW.origin_warehouse_id IS NOT NULL THEN
                    UPDATE inventory
                    SET theoretical_balance = theoretical_balance - NEW.quantity, updated_at = CURRENT_TIMESTAMP
                    WHERE warehouse_id = NEW.origin_warehouse_id
                        AND product_id = NEW.product_id
                        AND product_condition = NEW.product_condition;
                END IF;

                IF NEW.dest_warehouse_id IS NOT NULL THEN
                    UPDATE inventory
                    SET theoretical_balance = theoretical_balance + NEW.quantity, updated_at = CURRENT_TIMESTAMP
                    WHERE warehouse_id = NEW.dest_warehouse_id
                        AND product_id = NEW.product_id
                        AND product_condition = NEW.product_condition
                        AND theoretical_balance <= 2147483647 - NEW.quantity;

                    IF ROW_COUNT() = 0 THEN
                        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 30103, MESSAGE_TEXT = 'O saldo resultante excede o limite do estoque.';
                    END IF;
                END IF;
            END
            SQL);
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_atualiza_estoque_apos_movimento');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_prepara_estoque_antes_movimento');
    }
};
