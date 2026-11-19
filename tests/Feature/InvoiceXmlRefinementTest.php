<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceXmlRefinementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        $this->createSchema();
        $this->travelTo(Carbon::parse('2026-11-19 12:00:00', 'America/Sao_Paulo'));
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function catalog(): array
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::query()->create(['customer_name' => 'Cliente teste', 'customer_document' => '11111111000191', 'customer_payment_terms' => '30 dias']);
        $warehouse = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Teste']);
        foreach (['EXT-PAL-001', '0007'] as $index => $sku) {
            $product = Product::query()->create(['sku' => $sku, 'product_name' => $index === 0 ? 'PALETE FICTICIO XML A' : 'Outra descrição',
                'category' => 'PBR', 'material' => 'Madeira', 'cost_price' => 5, 'length_mm' => 1200, 'width_mm' => 1000]);
            DB::table('inventory')->insert(['warehouse_id' => $warehouse, 'product_id' => $product->product_id,
                'product_condition' => 'Novo', 'theoretical_balance' => 10]);
        }

        return ['customer_id' => $customer->customer_id, 'warehouse_id' => $warehouse,
            'product_condition' => 'Novo', 'payment_status' => 'A vencer', 'payment_terms_days' => 30];
    }

    private function upload(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('teste.xml', file_get_contents(base_path('tests/Fixtures/nfe-refinamentos-codigo.xml')));
    }

    public function test_preview_matches_codes_and_warns_about_description(): void
    {
        $this->catalog();
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload()])
            ->assertOk()->assertJsonPath('items.0.product_id', 1)
            ->assertJsonPath('items.0.description_mismatch', false)
            ->assertJsonPath('items.1.product_id', 2)
            ->assertJsonPath('items.1.description_mismatch', true)
            ->assertJsonPath('products.1.inventory.0.balance', 10);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_import_uses_emission_plus_days_and_ignores_submitted_due_date(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload(), 'due_date' => '2099-01-01'])
            ->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->assertSame('2026-12-13', $invoice->due_date->toDateString());
        $this->assertSame('', $invoice->payment_method);
        $this->assertSame('A vencer', $invoice->payment_status);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 8]);
        $this->assertDatabaseHas('inventory', ['product_id' => 2, 'theoretical_balance' => 7]);
        $this->assertDatabaseCount('invoice_items', 2);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertConflict();
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_unknown_code_is_not_resolved_by_old_mapping_and_blocks_all_writes(): void
    {
        $data = $this->catalog();
        Product::query()->whereKey(2)->update(['sku' => '7']);
        DB::table('product_xml_mappings')->insert(['issuer_document' => '99999999000191', 'external_code' => '0007', 'product_id' => 2]);
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload()])
            ->assertOk()->assertJsonPath('items.1.product_id', null);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])
            ->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 10]);
    }

    public function test_manual_product_override_is_rejected(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload(), 'items' => [['line_number' => 1, 'product_id' => 2]]])
            ->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_paid_import_requires_known_method_and_payment_date(): void
    {
        $data = [...$this->catalog(), 'payment_status' => 'Paga'];
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])
            ->assertUnprocessable()->assertJsonValidationErrors(['payment_method', 'payment_date']);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload(), 'payment_method' => 'inexistente', 'payment_date' => '2026-11-19'])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload(), 'payment_method' => 'PIX', 'payment_date' => '2026-11-19'])
            ->assertCreated();
        $this->assertDatabaseHas('invoices', ['payment_status' => 'Paga', 'payment_method' => 'PIX']);
    }

    public function test_pending_import_can_be_paid_later_without_changing_fiscal_data(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $url = '/api/invoices/'.$invoice->invoice_id;
        $page = $this->get('/consulta-nfe')->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML($page->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//form[@data-invoice-import-form]//input[@name="due_date" and @readonly]')->length);
        $this->assertSame(1, $xpath->query('//form[@data-invoice-import-form]//select[@name="payment_method" and @disabled]')->length);
        $this->assertSame(1, $xpath->query('//div[@data-imported-xml="true"]//select[@name="payment_method"]')->length);

        $this->putJson($url, ['payment_status' => 'Paga'])->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->putJson($url, ['payment_status' => 'Paga', 'payment_method' => 'Dinheiro', 'invoice_number' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('invoice_number');
        $this->putJson($url, ['payment_status' => 'Paga', 'payment_method' => 'Dinheiro'])->assertOk();
        $this->assertDatabaseHas('invoices', ['invoice_number' => 900001, 'payment_method' => 'Dinheiro', 'payment_status' => 'Paga']);
        $this->putJson($url, ['payment_status' => 'A vencer'])->assertOk();
        $this->assertDatabaseHas('invoices', ['payment_method' => '', 'payment_date' => null, 'payment_status' => 'A vencer']);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_zero_days_and_out_of_range_terms(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', [...$data, 'xml_file' => $this->upload(), 'payment_terms_days' => 2147483647])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_terms_days');
        $this->postJson('/api/invoices/import', [...$data, 'xml_file' => $this->upload(), 'payment_terms_days' => 0])->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->assertSame('2026-11-13', $invoice->due_date->toDateString());
        $this->assertSame('Vencida', $invoice->payment_status);
    }

    public function test_insufficient_stock_rolls_back_entire_import(): void
    {
        $data = $this->catalog();
        DB::table('inventory')->where('product_id', 2)->update(['theoretical_balance' => 1]);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertUnprocessable();
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 10]);
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
