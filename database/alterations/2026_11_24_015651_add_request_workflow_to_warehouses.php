<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::transaction(function (): void {
                DB::statement('ALTER TABLE warehouses ADD COLUMN warehouse_request_reason TEXT');
                DB::statement('ALTER TABLE warehouses ADD COLUMN warehouse_cancelation_reason TEXT');
                DB::statement("ALTER TABLE warehouses ADD COLUMN warehouse_situation TEXT NOT NULL DEFAULT 'Solicitado'
                    CHECK (warehouse_situation IN ('Ativo', 'Inativo', 'Solicitado', 'Recusado'))
                    CHECK (warehouse_situation <> 'Recusado' OR NULLIF(TRIM(warehouse_cancelation_reason), '') IS NOT NULL)");
                DB::table('warehouses')->update(['warehouse_situation' => 'Ativo']);
                DB::statement("ALTER TABLE warehouses ADD COLUMN requested_by INTEGER REFERENCES users(id) ON DELETE RESTRICT
                    CHECK (warehouse_situation <> 'Solicitado' OR (requested_by IS NOT NULL AND NULLIF(TRIM(warehouse_request_reason), '') IS NOT NULL))");
            });

            return;
        }

        if (! Schema::hasColumn('warehouses', 'warehouse_situation')) {
            DB::statement("ALTER TABLE warehouses
                ADD COLUMN warehouse_situation ENUM('Ativo','Inativo','Solicitado','Recusado') NULL,
                ADD COLUMN warehouse_request_reason TEXT NULL,
                ADD COLUMN warehouse_cancelation_reason TEXT NULL,
                ADD COLUMN requested_by BIGINT UNSIGNED NULL,
                ADD CONSTRAINT fk_warehouses_requested_by FOREIGN KEY (requested_by) REFERENCES users(id),
                ADD CONSTRAINT chk_warehouses_refusal CHECK (
                    warehouse_situation <> 'Recusado' OR NULLIF(TRIM(warehouse_cancelation_reason), '') IS NOT NULL),
                ADD CONSTRAINT chk_warehouses_request CHECK (
                    warehouse_situation <> 'Solicitado' OR (requested_by IS NOT NULL AND NULLIF(TRIM(warehouse_request_reason), '') IS NOT NULL))");
        }
        DB::table('warehouses')->whereNull('warehouse_situation')->update(['warehouse_situation' => 'Ativo']);
        DB::statement("ALTER TABLE warehouses MODIFY warehouse_situation ENUM('Ativo','Inativo','Solicitado','Recusado') NOT NULL DEFAULT 'Solicitado'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::transaction(function (): void {
                foreach (['requested_by', 'warehouse_situation', 'warehouse_cancelation_reason', 'warehouse_request_reason'] as $column) {
                    DB::statement("ALTER TABLE warehouses DROP COLUMN {$column}");
                }
            });

            return;
        }

        $dropCheck = str_contains(strtolower(DB::selectOne('SELECT VERSION() AS version')->version), 'mariadb') ? 'DROP CONSTRAINT' : 'DROP CHECK';
        DB::statement("ALTER TABLE warehouses {$dropCheck} chk_warehouses_request, {$dropCheck} chk_warehouses_refusal,
            DROP FOREIGN KEY fk_warehouses_requested_by, DROP COLUMN requested_by, DROP COLUMN warehouse_situation,
            DROP COLUMN warehouse_request_reason, DROP COLUMN warehouse_cancelation_reason");
    }
};
