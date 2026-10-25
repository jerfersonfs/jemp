<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            throw new RuntimeException('A tabela stock_movements não existe no banco conectado.');
        }

        if (Schema::hasColumn('stock_movements', 'sm_id')) {
            $smIdIsPrimaryKey = collect(Schema::getIndexes('stock_movements'))
                ->contains(fn (array $index): bool => ($index['primary'] ?? false) && $index['columns'] === ['sm_id']);

            if (! $smIdIsPrimaryKey) {
                throw new RuntimeException('A coluna sm_id já existe, mas não é a chave primária. Revise os dados antes de continuar.');
            }

            return;
        }

        $hasPrimaryKey = collect(Schema::getIndexes('stock_movements'))
            ->contains(fn (array $index): bool => $index['primary'] ?? false);

        if ($hasPrimaryKey) {
            throw new RuntimeException('stock_movements já possui uma chave primária. Revise-a antes de adicionar sm_id.');
        }

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->increments('sm_id')->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('stock_movements') || ! Schema::hasColumn('stock_movements', 'sm_id')) {
            return;
        }

        $smIdIsPrimaryKey = collect(Schema::getIndexes('stock_movements'))
            ->contains(fn (array $index): bool => ($index['primary'] ?? false) && $index['columns'] === ['sm_id']);

        if (! $smIdIsPrimaryKey) {
            throw new RuntimeException('sm_id não é a chave primária atual; a coluna foi preservada no rollback.');
        }

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropPrimary();
            $table->dropColumn('sm_id');
        });
    }
};
