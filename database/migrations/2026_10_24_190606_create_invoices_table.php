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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');

             $table->foreignId('customer_id')
            ->constrained('customers', 'customer_id')
            ->cascadeOnUpdate()
            ->restrictOnDelete();

            $table->integer('invoice_number');
            $table->integer('invoice_serie');

            $table->date('issue_date');
            $table->date('due_date');
            $table->string('payment_method', 50);
            $table->string('delivery_driver', 100) ->nullable();
            $table->integer('payment_terms_days');
            $table->enum('payment_status', [
            'A vencer',
            'Vencida',
            'Paga',
            'Cancelada'
            ])->default('A vencer');
            $table->date('payment_date') ->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->unique(
            ['invoice_number', 'invoice_serie'],
            'uq_invoices_number_serie'
            );
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
        Schema::dropIfExists('invoices');
    }
};
