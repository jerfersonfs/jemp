<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->integer('invoice_number');
            $table->integer('invoice_serie');

            // Chave Estrangeira para customers
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id', 'fk_invoices_customer')
            ->references('customer_id')
            ->on('customers')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('payment_method', 50);
            $table->string('delivery_driver', 100)->nullable();
            $table->integer('payment_terms_days');
            $table->enum('payment_status', ['A vencer', 'Vencida', 'Paga', 'Cancelada'])->default('A vencer');
            $table->date('payment_date')->nullable();

            $table->unique(['invoice_number', 'invoice_serie'], 'uq_invoices_number_serie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
