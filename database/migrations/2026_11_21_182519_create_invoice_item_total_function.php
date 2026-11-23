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

        DB::unprepared(<<<'FUNCTION_SQL'
            CREATE FUNCTION fn_total_item_nota(p_quantidade INT, p_preco_unitario DECIMAL(10,2))
            RETURNS DECIMAL(20,2)
            DETERMINISTIC
            NO SQL
            SQL SECURITY INVOKER
            RETURN p_quantidade * p_preco_unitario
            FUNCTION_SQL);
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::unprepared('DROP FUNCTION IF EXISTS fn_total_item_nota');
        }
    }
};
