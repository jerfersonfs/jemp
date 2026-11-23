<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedInteger('low_stock_threshold');
        });

        DB::table('company_settings')->insert(['id' => 1, 'low_stock_threshold' => 20]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
