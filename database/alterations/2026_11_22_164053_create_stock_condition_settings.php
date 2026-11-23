<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_condition_settings', function (Blueprint $table): void {
            $table->string('product_condition', 30)->primary();
            $table->boolean('saleable');
        });
        DB::table('stock_condition_settings')->insert([
            ['product_condition' => 'Novo', 'saleable' => true],
            ['product_condition' => 'Reforma', 'saleable' => true],
            ['product_condition' => 'Descarte', 'saleable' => false],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_condition_settings');
    }
};
