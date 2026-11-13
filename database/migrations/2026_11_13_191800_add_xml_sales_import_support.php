<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('issuer_document', 14)->default('');
            $table->string('access_key', 44)->nullable()->unique('uq_invoices_access_key');
            $table->timestamp('stock_posted_at')->nullable();
        });

        foreach (Schema::getIndexes('invoices') as $index) {
            if ($index['unique'] && $index['columns'] === ['invoice_number', 'invoice_serie']) {
                Schema::table('invoices', fn (Blueprint $table) => $table->dropUnique($index['name']));
            }
        }

        Schema::table('invoices', function (Blueprint $table): void {
            $table->unique(['issuer_document', 'invoice_number', 'invoice_serie'], 'uq_invoices_issuer_number_serie');
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->unsignedSmallInteger('xml_line_number')->nullable();
            $table->string('external_code', 60)->nullable();
            $table->string('external_description', 120)->nullable();
            $table->unique(['invoice_id', 'xml_line_number'], 'uq_invoice_items_xml_line');
        });

        Schema::create('product_xml_mappings', function (Blueprint $table): void {
            $table->id();
            $table->string('issuer_document', 14);
            $code = $table->string('external_code', 60);
            if (DB::getDriverName() === 'mysql') {
                $code->collation('utf8mb4_bin');
            }
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('product_id')->on('products')->restrictOnDelete()->cascadeOnUpdate();
            $table->unique(['issuer_document', 'external_code'], 'uq_product_xml_issuer_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::table('invoices')->whereNotNull('access_key')->exists() || DB::table('product_xml_mappings')->exists()) {
            throw new RuntimeException('O rollback apagaria referências de XML já utilizadas. Preserve os dados e aplique uma migration corretiva.');
        }

        Schema::dropIfExists('product_xml_mappings');
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->dropUnique('uq_invoice_items_xml_line');
            $table->dropColumn(['xml_line_number', 'external_code', 'external_description']);
        });
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('uq_invoices_issuer_number_serie');
            $table->dropUnique('uq_invoices_access_key');
            $table->dropColumn(['issuer_document', 'access_key', 'stock_posted_at']);
            $table->unique(['invoice_number', 'invoice_serie'], 'uq_invoices_number_serie');
        });
    }
};
