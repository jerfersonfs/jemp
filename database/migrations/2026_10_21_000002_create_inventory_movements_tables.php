<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('location');
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'location']);
        });

        DB::table('products')->select(['id', 'location', 'quantity'])->orderBy('id')->chunk(500, function ($products): void {
            foreach ($products as $product) {
                DB::table('product_stocks')->insert([
                    'product_id' => $product->id,
                    'location' => $product->location,
                    'quantity' => $product->quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        Schema::create('movements', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('direction');
            $table->string('type');
            $table->unsignedInteger('quantity');
            $table->dateTime('occurred_at');
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('lot')->nullable();
            $table->string('recipient')->nullable();
            $table->string('reference')->nullable();
            $table->string('responsible')->nullable();
            $table->string('condition')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['direction', 'occurred_at']);
        });

        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('sold_at');
            $table->string('invoice')->nullable();
            $table->string('series')->nullable();
            $table->string('payment')->nullable();
            $table->string('status')->default('Pendente');
            $table->string('warehouse');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'sold_at']);
        });

        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_code');
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('movements');
        Schema::dropIfExists('product_stocks');
    }
};
