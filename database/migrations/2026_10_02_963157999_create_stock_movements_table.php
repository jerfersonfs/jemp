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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('origin_warehouse_id')->nullable();
            $table->unsignedBigInteger('dest_warehouse_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition', 30);

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'fk_stock_movements_user')
                  ->references('id') // Talvez mudar para user_id
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id', 'fk_stock_movements_invoice')
                  ->references('invoice_id')
                  ->on('invoices')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->foreign('product_id', 'fk_stock_movements_product')
                  ->references('product_id')
                  ->on('products')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->integer('quantity');
            $table->enum('movement_type', ['Entrada', 'Saída', 'Transferência']);
            $table->string('movement_justification', 255)->nullable();
            $table->dateTime('movement_date')->useCurrent();
            $table->boolean('is_fiscal')->default(false);

            // Chaves compostas
            $table->foreign(['origin_warehouse_id', 'product_id', 'product_condition'], 'fk_stock_movements_origin_inventory')
                  ->references(['warehouse_id', 'product_id', 'product_condition'])
                  ->on('inventory')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->foreign(['dest_warehouse_id', 'product_id', 'product_condition'], 'fk_stock_movements_dest_inventory')
                  ->references(['warehouse_id', 'product_id', 'product_condition'])
                  ->on('inventory')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
