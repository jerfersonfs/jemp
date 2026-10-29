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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->enum('category', ['PBR', 'Descartável', 'Não Standard']);
            $table->string('product_name', 150)->nullable();
            $table->string('material', 100);
            $table->decimal('cost_price', 10, 2);
            $table->integer('length_mm');
            $table->integer('width_mm');

            $table->unique(['product_name', 'category', 'length_mm', 'width_mm', 'material'], 'uq_products_specification');
            $table->timestamps();
        });

        DB::unprepared('ALTER TABLE `products` ADD CONSTRAINT `chk_products_cost_price` CHECK (`cost_price` >= 0);');
        DB::unprepared('ALTER TABLE `products` ADD CONSTRAINT `chk_products_length` CHECK (`length_mm` > 0);');
        DB::unprepared('ALTER TABLE `products` ADD CONSTRAINT `chk_products_width` CHECK (`width_mm` > 0);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
