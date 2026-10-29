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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id('item_id');

            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id', 'fk_invoice_items_invoice')
            ->references('invoice_id')
            ->on('invoices')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id', 'fk_invoice_items_product')
            ->references('product_id')
            ->on('products')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 10 ,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
