<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount', 10, 2);
        });

        DB::statement('
                ALTER TABLE invoices
                ADD CONSTRAINT chk_invoices_payment_terms
                CHECK (payment_terms_days >= 0)
            ');

            DB::statement("
                ALTER TABLE invoices
                ADD CONSTRAINT chk_invoices_payment_date
                CHECK (
                    (payment_status = 'Paga' AND payment_date IS NOT NULL)
                    OR
                    (payment_status <> 'Paga' AND payment_date IS NULL)
                )
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE invoices DROP CHECK chk_invoices_payment_terms');
        DB::statement('ALTER TABLE invoices DROP CHECK chk_invoices_payment_date');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
