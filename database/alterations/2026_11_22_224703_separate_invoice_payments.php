<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $mysql = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table): void {
                $table->id('payment_id');
                $table->foreignId('invoice_id')->unique()->constrained('invoices', 'invoice_id')->restrictOnDelete();
                $table->unsignedInteger('payment_terms_days');
                $table->date('due_date');
                $table->unsignedSmallInteger('installment_count')->default(1);
                $table->string('payment_method', 50)->nullable();
                $table->date('paid_at')->nullable();
                $table->index(['paid_at', 'due_date']);
            });
        } elseif (! Schema::hasColumns('payments', ['installment_count', 'payment_terms_days'])) {
            throw new RuntimeException('A tabela payments já existe com outra estrutura. Confira os registros antes de migrar.');
        }
        if (! Schema::hasColumn('invoices', 'is_cancelled')) {
            Schema::table('invoices', fn (Blueprint $table) => $table->boolean('is_cancelled')->default(false));
        }

        if (Schema::hasColumn('invoices', 'payment_status')) {
            DB::transaction(function (): void {
                DB::statement('UPDATE invoices SET amount = (SELECT SUM(ii.quantity * ii.unit_price) FROM invoice_items ii WHERE ii.invoice_id = invoices.invoice_id)
                    WHERE amount = 0 AND EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.invoice_id = invoices.invoice_id)');
                if (DB::table('invoices')->where('payment_status', 'Paga')->whereNull('payment_date')->exists()) {
                    throw new RuntimeException('Há notas pagas sem data de recebimento. Confira os dados históricos antes de migrar.');
                }
                DB::table('invoices')->where('payment_status', 'Cancelada')->update(['is_cancelled' => true]);
                DB::statement("INSERT INTO payments (invoice_id, payment_terms_days, due_date, installment_count, payment_method, paid_at)
                    SELECT i.invoice_id, i.payment_terms_days, i.due_date, 1, NULLIF(i.payment_method, ''),
                        CASE WHEN i.payment_status = 'Paga' THEN i.payment_date END
                    FROM invoices i WHERE NOT EXISTS (SELECT 1 FROM payments p WHERE p.invoice_id = i.invoice_id)");
            });
        }

        if ($mysql) {
            $this->createRecordingProcedure();
            $this->createFinancialViews();
            $this->dropLegacyCheck('invoices', 'chk_invoices_payment_terms');
            $this->dropLegacyCheck('invoices', 'chk_invoices_payment_date');
            $this->dropLegacyCheck('customers', 'chk_customers_payment_terms');
        }
        $old = array_intersect(['due_date', 'payment_terms_days', 'payment_status', 'payment_date', 'payment_method'], Schema::getColumnListing('invoices'));
        if ($old) {
            Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(array_values($old)));
        }
        if (Schema::hasColumn('customers', 'customer_payment_terms')) {
            Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('customer_payment_terms'));
        }

    }

    private function dropLegacyCheck(string $table, string $name): void
    {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')->whereRaw('CONSTRAINT_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)->where('CONSTRAINT_NAME', $name)->exists();
        if ($exists) {
            $drop = str_contains(strtolower(DB::selectOne('SELECT VERSION() AS v')->v), 'mariadb') ? 'DROP CONSTRAINT' : 'DROP CHECK';
            DB::statement("ALTER TABLE {$table} {$drop} {$name}");
        }
    }

    private function createFinancialViews(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW vw_notas_fiscais AS
            SELECT i.invoice_id, i.invoice_number, i.invoice_serie, i.issue_date, i.is_cancelled,
                c.customer_id, c.customer_name, c.customer_document, i.amount AS total_value,
                p.payment_id, p.payment_terms_days, p.due_date, p.installment_count, p.payment_method, p.paid_at AS payment_date,
                CASE WHEN i.is_cancelled THEN 'Cancelada'
                     WHEN p.payment_id IS NULL THEN 'Sem pagamento cadastrado'
                     WHEN p.paid_at IS NOT NULL THEN 'Paga'
                     WHEN p.due_date < DATE(COALESCE(CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', 'America/Sao_Paulo'), UTC_TIMESTAMP() - INTERVAL 3 HOUR)) THEN 'Vencida'
                     ELSE 'A vencer' END AS payment_status
            FROM invoices i JOIN customers c ON c.customer_id = i.customer_id
            LEFT JOIN payments p ON p.invoice_id = i.invoice_id
            SQL);
        DB::statement("CREATE OR REPLACE VIEW vw_notas_vencidas AS SELECT * FROM vw_notas_fiscais WHERE payment_status = 'Vencida'");
    }

    private function createRecordingProcedure(): void
    {
        DB::statement('DROP PROCEDURE IF EXISTS sp_registrar_pagamento_nota');
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_registrar_pagamento_nota(
                IN p_invoice_id BIGINT UNSIGNED, IN p_days INT, IN p_count INT,
                IN p_method VARCHAR(50), IN p_paid_at DATE
            )
            MODIFIES SQL DATA SQL SECURITY INVOKER
            BEGIN
                DECLARE v_issue_date DATE;
                DECLARE v_amount DECIMAL(10,2);
                SELECT issue_date, amount INTO v_issue_date, v_amount FROM invoices
                    WHERE invoice_id = p_invoice_id AND is_cancelled = 0;
                IF v_issue_date IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nota inexistente ou cancelada.';
                END IF;
                IF p_days IS NULL OR p_days < 0 OR p_days > DATEDIFF('9999-12-31', v_issue_date) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Prazo de pagamento inválido.';
                END IF;
                IF p_count IS NULL OR p_count < 1 OR p_count > LEAST(65535, GREATEST(1, v_amount * 100)) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantidade de parcelas inválida para o total da venda.';
                END IF;
                IF p_paid_at IS NOT NULL AND NULLIF(TRIM(p_method), '') IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe a forma de pagamento da venda recebida.';
                END IF;
                INSERT INTO payments (invoice_id, payment_terms_days, due_date, installment_count, payment_method, paid_at)
                VALUES (p_invoice_id, p_days, DATE_ADD(v_issue_date, INTERVAL p_days DAY), p_count, NULLIF(TRIM(p_method), ''), p_paid_at);
            END
            SQL);
    }

    public function down(): void
    {
        throw new RuntimeException('A condição de pagamento antiga dos clientes só pode ser recuperada de um backup anterior à migração.');
    }
};
