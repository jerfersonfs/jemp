<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait SetsUpInvoicePayments
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        $this->createSchema();
        if ($this->migrateOnSetup()) {
            $this->migratePayments();
        }
        DB::statement('CREATE VIEW vw_estoque_baixo AS SELECT product_id, product_name, 0 AS theoretical_balance FROM products WHERE 0');
        (require database_path('alterations/2026_11_22_164053_create_stock_condition_settings.php'))->up();
        $this->travelTo(Carbon::parse('2026-11-19 12:00:00', 'America/Sao_Paulo'));
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    protected function catalog(): array
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::query()->create(['customer_name' => 'Cliente teste', 'customer_document' => '11111111000191']);
        $warehouse = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Teste', 'warehouse_situation' => 'Ativo']);
        foreach (['EXT-PAL-001', '0007'] as $index => $sku) {
            $product = Product::query()->create(['sku' => $sku, 'product_name' => $index === 0 ? 'PALETE FICTICIO XML A' : 'Outra descrição',
                'category' => 'PBR', 'material' => 'Madeira', 'cost_price' => 5, 'length_mm' => 1200, 'width_mm' => 1000]);
            DB::table('inventory')->insert(['warehouse_id' => $warehouse, 'product_id' => $product->product_id,
                'product_condition' => 'Novo', 'theoretical_balance' => 10]);
        }

        return ['customer_id' => $customer->customer_id, 'warehouse_id' => $warehouse,
            'product_condition' => 'Novo', 'payment_terms_days' => 30, 'payment_status' => 'A vencer'];
    }

    protected function upload(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('teste.xml', file_get_contents(base_path('tests/Fixtures/nfe-refinamentos-codigo.xml')));
    }

    protected function migrateOnSetup(): bool
    {
        return true;
    }

    protected function migratePayments(): void
    {
        (require database_path('alterations/2026_11_22_224703_separate_invoice_payments.php'))->up();
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table): void {
            $table->id('customer_id');
            $table->string('customer_name');
            $table->string('customer_document')->unique();
            $table->string('customer_contact')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_payment_terms');
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id('product_id');
            $table->string('sku')->unique();
            $table->string('product_name');
            $table->string('category');
            $table->string('material');
            $table->decimal('cost_price', 10, 2);
            $table->integer('length_mm');
            $table->integer('width_mm');
            $table->timestamps();
        });
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id('warehouse_id');
            $table->string('warehouse_name');
            $table->text('warehouse_description')->nullable();
            $table->timestamps();
        });
        (require database_path('alterations/2026_11_24_015651_add_request_workflow_to_warehouses.php'))->up();
        Schema::create('inventory', function (Blueprint $table): void {
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition');
            $table->integer('theoretical_balance');
            $table->timestamps();
            $table->primary(['warehouse_id', 'product_id', 'product_condition']);
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id('invoice_id');
            $table->integer('invoice_number');
            $table->integer('invoice_serie');
            $table->unsignedBigInteger('customer_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('payment_method');
            $table->string('delivery_driver')->nullable();
            $table->integer('payment_terms_days');
            $table->string('payment_status')->default('A vencer');
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['invoice_number', 'invoice_serie'], 'uq_invoices_number_serie');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id('item_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id('sm_id');
            $table->unsignedBigInteger('origin_warehouse_id')->nullable();
            $table->unsignedBigInteger('dest_warehouse_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->integer('quantity');
            $table->string('movement_type');
            $table->string('movement_justification')->nullable();
            $table->dateTime('movement_date');
            $table->boolean('is_fiscal');
            $table->timestamps();
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->foreign(['origin_warehouse_id', 'product_id', 'product_condition'])->references(['warehouse_id', 'product_id', 'product_condition'])->on('inventory');
        });
        (require database_path('migrations/2026_11_13_191800_add_xml_sales_import_support.php'))->up();
    }
}
