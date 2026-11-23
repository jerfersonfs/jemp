<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductManualCodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        $this->createSchema();
        (require database_path('migrations/2026_11_21_185033_create_company_settings_table.php'))->up();
        (require database_path('alterations/2026_11_22_164053_create_stock_condition_settings.php'))->up();
        DB::statement('CREATE VIEW vw_estoque AS SELECT i.*, p.sku, p.category, p.product_name, w.warehouse_name
            FROM inventory i JOIN products p ON p.product_id = i.product_id JOIN warehouses w ON w.warehouse_id = i.warehouse_id');
        (require database_path('alterations/2026_11_22_164231_apply_saleable_conditions_to_stock_view.php'))->up();
        DB::statement("CREATE VIEW vw_estoque_baixo AS SELECT * FROM vw_estoque_atual WHERE stock_status IN ('Estoque baixo', 'Zerado')");
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function payload(string $code = '008'): array
    {
        return ['sku' => $code, 'product_name' => 'Palete manual', 'category' => 'PBR',
            'material' => 'Madeira', 'cost_price' => 35.50, 'length_mm' => 1200, 'width_mm' => 1000];
    }

    public function test_manual_code_preserves_leading_zeroes(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/products', $this->payload())
            ->assertCreated()->assertJsonPath('data.sku', '008');
        $this->assertDatabaseHas('products', ['sku' => '008']);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_duplicate_code_returns_422_without_creating_product(): void
    {
        Product::query()->create($this->payload());
        $this->actingAs(User::factory()->create())
            ->postJson('/api/products', [...$this->payload(), 'product_name' => 'Outro produto'])
            ->assertUnprocessable()->assertJsonValidationErrors('sku');
        $this->assertDatabaseCount('products', 1);
    }

    public function test_missing_or_invalid_code_returns_422(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([null, '', '   ', 8, str_repeat('A', 256)] as $code) {
            $this->postJson('/api/products', [...$this->payload(), 'sku' => $code])
                ->assertUnprocessable()->assertJsonValidationErrors('sku');
        }
        $this->assertDatabaseCount('products', 0);
    }

    public function test_editing_code_preserves_product_id_and_xml_mapping(): void
    {
        $product = Product::query()->create($this->payload());
        DB::table('product_xml_mappings')->insert(['issuer_document' => '12345678000190',
            'external_code' => 'COD-XML', 'product_id' => $product->product_id,
            'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs(User::factory()->create())
            ->putJson('/api/products/'.$product->product_id, $this->payload('PAL-025'))
            ->assertOk()->assertJsonPath('data.sku', 'PAL-025');
        $this->assertDatabaseHas('products', ['product_id' => $product->product_id, 'sku' => 'PAL-025']);
        $this->assertDatabaseHas('product_xml_mappings', ['external_code' => 'COD-XML', 'product_id' => $product->product_id]);
    }

    public function test_edit_accepts_own_code_but_rejects_another_products_code(): void
    {
        $product = Product::query()->create($this->payload());
        Product::query()->create([...$this->payload('OUTRO'), 'product_name' => 'Outro produto']);
        $this->actingAs(User::factory()->create());
        $this->putJson('/api/products/'.$product->product_id, $this->payload())->assertOk();
        $this->putJson('/api/products/'.$product->product_id, $this->payload('OUTRO'))
            ->assertUnprocessable()->assertJsonValidationErrors('sku');
        $this->assertDatabaseHas('products', ['product_id' => $product->product_id, 'sku' => '008']);
    }

    public function test_web_form_accepts_manual_code_and_initial_inventory(): void
    {
        $warehouse = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Teste'], 'warehouse_id');
        $this->actingAs(User::factory()->create())
            ->post(route('products.store'), [...$this->payload(), 'warehouse_id' => $warehouse,
                'product_condition' => 'Novo', 'theoretical_balance' => 5])
            ->assertRedirect(route('products.index'))->assertSessionHasNoErrors();
        $product = Product::query()->sole();
        $this->assertSame('008', $product->sku);
        $this->assertDatabaseHas('inventory', ['product_id' => $product->product_id, 'theoretical_balance' => 5]);
        $response = $this->get(route('products.index'))->assertOk()->assertSee('Código do produto');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $input = (new \DOMXPath($dom))->query('//input[@id="product-sku"]')->item(0);
        $this->assertNotNull($input);
        $this->assertFalse($input->hasAttribute('readonly'));
        $this->assertTrue($input->hasAttribute('required'));
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
