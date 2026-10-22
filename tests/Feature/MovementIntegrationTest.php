<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_adds_stock_and_is_listed_in_the_entry_history(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'location' => 'Barracão 1']);

        $this->actingAs($user)->post(route('entrance.store'), [
            'date' => '2026-10-21T10:30',
            'type' => 'Compra',
            'product_id' => $product->id,
            'quantity' => 5,
            'origin' => 'Fornecedor Central',
            'destination' => 'Barracão 2',
            'justification' => 'Reposição',
        ])->assertRedirect(route('entrance.index'));

        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 2', 'quantity' => 5]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 15]);
        $this->assertDatabaseHas('movements', ['product_id' => $product->id, 'direction' => 'entry', 'quantity' => 5]);
    }

    public function test_exit_rejects_a_quantity_greater_than_stock_without_changing_inventory(): void
    {
        $product = Product::factory()->create(['quantity' => 3, 'location' => 'Barracão 1']);

        $this->actingAs(User::factory()->create())->from(route('exits.index'))->post(route('exits.store'), [
            'date' => '2026-10-21',
            'type' => 'Venda',
            'product_id' => $product->id,
            'quantity' => 4,
            'warehouse' => 'Barracão 1',
        ])->assertRedirect(route('exits.index'))->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 1', 'quantity' => 3]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseCount('movements', 0);
    }

    public function test_transfer_moves_stock_between_locations_without_changing_total_quantity(): void
    {
        $product = Product::factory()->create(['quantity' => 8, 'location' => 'Barracão 1']);

        $this->actingAs(User::factory()->create())->post(route('internal.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
            'origin' => 'Barracão 1',
            'destination' => 'Barracão 2',
            'condition' => 'Bom',
        ])->assertRedirect(route('internal.index'));

        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 1', 'quantity' => 5]);
        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'location' => 'Barracão 2', 'quantity' => 3]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 8]);
        $this->assertDatabaseHas('movements', ['product_id' => $product->id, 'direction' => 'transfer', 'quantity' => 3]);
    }
}
