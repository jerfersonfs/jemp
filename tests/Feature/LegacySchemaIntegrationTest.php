<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacySchemaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        foreach (['stock_movements', 'inventory', 'invoice_items', 'invoices', 'sale_items', 'sales', 'movements', 'product_stocks', 'products', 'customers', 'warehouses'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        Schema::create('products', function (Blueprint $table): void {
            $table->bigIncrements('product_id');
            $table->string('sku')->unique();
            $table->string('category');
            $table->string('product_name', 150);
            $table->string('material', 100);
            $table->decimal('cost_price', 10, 2);
            $table->integer('length_mm');
            $table->integer('width_mm');
            $table->timestamps();
            $table->unique(['product_name', 'category', 'length_mm', 'width_mm', 'material']);
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->bigIncrements('customer_id');
            $table->string('customer_name', 150);
            $table->string('customer_document', 20)->unique();
            $table->string('customer_contact', 30)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_payment_terms');
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table): void {
            $table->bigIncrements('warehouse_id');
            $table->string('warehouse_name');
            $table->text('warehouse_description')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table): void {
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition', 30);
            $table->integer('theoretical_balance')->default(0);
            $table->timestamps();
            $table->primary(['warehouse_id', 'product_id', 'product_condition']);
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses');
            $table->foreign('product_id')->references('product_id')->on('products');
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->bigIncrements('invoice_id');
            $table->integer('invoice_number');
            $table->integer('invoice_serie');
            $table->unsignedBigInteger('customer_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('payment_method', 50);
            $table->string('delivery_driver', 100)->nullable();
            $table->integer('payment_terms_days');
            $table->string('payment_status', 20)->default('A vencer');
            $table->date('payment_date')->nullable();
            $table->timestamps();
            $table->unique(['invoice_number', 'invoice_serie']);
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->bigIncrements('item_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
            $table->foreign('product_id')->references('product_id')->on('products');
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->unsignedBigInteger('origin_warehouse_id')->nullable();
            $table->unsignedBigInteger('dest_warehouse_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition', 30);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->integer('quantity');
            $table->string('movement_type', 20);
            $table->string('movement_justification')->nullable();
            $table->dateTime('movement_date');
            $table->boolean('is_fiscal')->default(false);
            $table->timestamps();
            $table->foreign('origin_warehouse_id')->references('warehouse_id')->on('warehouses');
            $table->foreign('dest_warehouse_id')->references('warehouse_id')->on('warehouses');
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
        });
    }

    public function test_product_crud_uses_legacy_columns_and_inventory_balance(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouseId = $this->createWarehouse();

        $this->post(route('products.store'), $this->validProduct($warehouseId))
            ->assertRedirect(route('products.index'));

        $product = Product::query()->sole();
        $this->assertDatabaseHas('products', [
            'product_id' => $product->product_id,
            'sku' => 'PBR-TEST-002',
            'product_name' => 'Palete de teste',
            'width_mm' => 1000,
            'length_mm' => 1200,
        ]);
        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $warehouseId,
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 12,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('PBR-TEST-002')
            ->assertSee('Palete de teste')
            ->assertSee('1200');

        $this->patch(route('products.update', $product), array_merge($this->validProduct($warehouseId), [
            'sku' => 'PBR-TEST-003',
            'theoretical_balance' => 15,
        ]))->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['product_id' => $product->product_id, 'sku' => 'PBR-TEST-003']);
        $this->assertDatabaseHas('inventory', ['product_id' => $product->product_id, 'theoretical_balance' => 15]);

        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['product_id' => $product->product_id]);
        $this->assertDatabaseMissing('inventory', ['product_id' => $product->product_id]);
    }

    public function test_customer_crud_uses_only_existing_customer_attributes(): void
    {
        $this->actingAs(User::factory()->create());
        $customerData = [
            'customer_name' => 'Cliente de teste',
            'customer_document' => '12345678000190',
            'customer_contact' => '11999990000',
            'customer_email' => 'cliente@example.test',
            'customer_payment_terms' => '30 dias',
        ];

        $this->post(route('customers.store'), $customerData)->assertRedirect(route('customers.index'));
        $customer = Customer::query()->sole();

        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Cliente de teste')
            ->assertSee('30 dias');

        $this->patch(route('customers.update', $customer), array_merge($customerData, [
            'customer_name' => 'Cliente atualizado',
            'customer_document' => '10987654000100',
        ]))->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'customer_id' => $customer->customer_id,
            'customer_name' => 'Cliente atualizado',
            'customer_document' => '10987654000100',
        ]);

        $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['customer_id' => $customer->customer_id]);
    }

    public function test_movement_pages_read_and_write_the_existing_stock_movement_and_inventory_tables(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouseId = $this->createWarehouse();
        $destinationWarehouseId = $this->createWarehouse('Warehouse 2');
        $product = Product::query()->create([
            'sku' => 'PBR-MOV-001',
            'category' => 'PBR',
            'product_name' => 'Palete para movimento',
            'material' => 'Madeira',
            'cost_price' => 35.50,
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);

        DB::table('inventory')->insert([
            'warehouse_id' => $warehouseId,
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('entrance.store'), [
            'date' => '2026-10-22T09:00',
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'quantity' => 2,
            'dest_warehouse_id' => $warehouseId,
            'movement_justification' => 'Entrada de teste',
        ])->assertRedirect(route('entrance.index'));

        $this->post(route('internal.store'), [
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'quantity' => 3,
            'origin_warehouse_id' => $warehouseId,
            'dest_warehouse_id' => $destinationWarehouseId,
            'movement_justification' => 'Transferência de teste',
        ])->assertRedirect(route('internal.index'));

        $this->post(route('exits.store'), [
            'date' => '2026-10-22',
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'quantity' => 2,
            'origin_warehouse_id' => $warehouseId,
            'movement_justification' => 'Saída de teste',
        ])->assertRedirect(route('exits.index'));

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $warehouseId,
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 5,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->product_id,
            'movement_type' => 'Saída',
            'quantity' => 2,
            'origin_warehouse_id' => $warehouseId,
            'user_id' => auth()->id(),
        ]);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $destinationWarehouseId,
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 3,
        ]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->product_id, 'movement_type' => 'Entrada', 'quantity' => 2]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->product_id, 'movement_type' => 'Transferência', 'quantity' => 3]);
        $this->get(route('entrance.index'))->assertOk()->assertSee('Palete para movimento')->assertSee('Entrada de teste');
        $this->get(route('internal.index'))->assertOk()->assertSee('Transferência de teste');
        $this->get(route('exits.index'))->assertOk()->assertSee('Palete para movimento')->assertSee('Saída de teste');
    }

    public function test_sales_and_invoice_pages_list_existing_invoice_data_without_a_sales_table(): void
    {
        $this->actingAs(User::factory()->create());
        $customerId = DB::table('customers')->insertGetId([
            'customer_name' => 'Cliente NF',
            'customer_document' => '12312312300',
            'customer_contact' => null,
            'customer_email' => null,
            'customer_payment_terms' => 'À vista',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'customer_id');
        $productId = DB::table('products')->insertGetId([
            'sku' => 'NF-PROD-001',
            'category' => 'PBR',
            'product_name' => 'Produto NF',
            'material' => 'Madeira',
            'cost_price' => 10,
            'length_mm' => 1000,
            'width_mm' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'product_id');
        $invoiceId = DB::table('invoices')->insertGetId([
            'invoice_number' => 42,
            'invoice_serie' => 1,
            'customer_id' => $customerId,
            'issue_date' => '2026-10-22',
            'due_date' => '2026-10-22',
            'payment_method' => 'Pix',
            'delivery_driver' => null,
            'payment_terms_days' => 0,
            'payment_status' => 'Paga',
            'payment_date' => '2026-10-22',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'invoice_id');
        DB::table('invoice_items')->insert([
            'invoice_id' => $invoiceId,
            'product_id' => $productId,
            'quantity' => 2,
            'unit_price' => 12.50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('sales.index'))
            ->assertOk()
            ->assertSee('Cliente NF')
            ->assertSee('25,00');
        $this->get(route('invoice-consult.index'))
            ->assertOk()
            ->assertSee('42')
            ->assertSee('Cliente NF')
            ->assertSee('25,00');
    }

    private function createWarehouse(string $name = 'Service Base'): int
    {
        return (int) DB::table('warehouses')->insertGetId([
            'warehouse_name' => $name,
            'warehouse_description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'warehouse_id');
    }

    /** @return array<string, int|string|float> */
    private function validProduct(int $warehouseId): array
    {
        return [
            'sku' => 'PBR-TEST-002',
            'category' => 'PBR',
            'product_name' => 'Palete de teste',
            'material' => 'Madeira',
            'cost_price' => 35.50,
            'length_mm' => 1200,
            'width_mm' => 1000,
            'warehouse_id' => $warehouseId,
            'product_condition' => 'Novo',
            'theoretical_balance' => 12,
        ];
    }
}
