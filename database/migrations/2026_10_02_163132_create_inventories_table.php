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
        Schema::create('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition', 30);
            $table->integer('theoretical_balance')->default(0);

            $table->primary(['warehouse_id', 'product_id', 'product_condition'], 'pk_inventory');
            $table->foreign('warehouse_id', 'fk_inventory_warehouse')
                  ->references('warehouse_id')
                  ->on('warehouses')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->foreign('product_id', 'fk_inventory_product')
                  ->references('product_id')
                  ->on('products')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->timestamps();
        });

        DB::unprepared('ALTER TABLE `inventory` ADD CONSTRAINT `chk_inventory_balance` CHECK (`theoretical_balance` >= 0);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
