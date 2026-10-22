<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_sale_persists_items_and_reduces_stock(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'location' => 'Barracão 1']);

        $this->actingAs(User::factory()->create())->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'date' => '2026-10-21',
            'status' => 'Concluída',
            'warehouse' => 'Barracão 1',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 12.50,
                'discount' => 2.50,
            ]],
        ])->assertRedirect(route('sales.index'));

        $this->assertDatabaseHas('sales', ['customer_id' => $customer->id, 'subtotal' => '25.00', 'discount' => '2.50', 'total' => '22.50']);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'quantity' => 2, 'total' => '22.50']);
        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 1', 'quantity' => 8]);
        $this->assertDatabaseHas('movements', ['product_id' => $product->id, 'direction' => 'exit', 'type' => 'Venda', 'quantity' => 2]);
    }

    public function test_completed_sale_rejects_insufficient_stock_without_saving_sale(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['quantity' => 1, 'location' => 'Barracão 1']);

        $this->actingAs(User::factory()->create())->from(route('sales.index'))->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'date' => '2026-10-21',
            'status' => 'Concluída',
            'warehouse' => 'Barracão 1',
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10, 'discount' => 0]],
        ])->assertRedirect(route('sales.index'))->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 1', 'quantity' => 1]);
    }

    public function test_sale_rejects_duplicate_product_rows(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'location' => 'Barracão 1']);
        $saleData = [
            'customer_id' => $customer->id,
            'date' => '2026-10-21',
            'status' => 'Concluída',
            'warehouse' => 'Barracão 1',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 6, 'unit_price' => 10, 'discount' => 0],
                ['product_id' => $product->id, 'quantity' => 6, 'unit_price' => 10, 'discount' => 0],
            ],
        ];

        $this->actingAs(User::factory()->create())
            ->from(route('sales.index'))
            ->post(route('sales.store'), $saleData)
            ->assertRedirect(route('sales.index'))
            ->assertSessionHasErrors('items.1.product_id');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 1', 'quantity' => 10]);
    }
}
