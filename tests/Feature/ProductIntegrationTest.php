<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_and_delete_product(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('products.store'), $this->validProduct())
            ->assertRedirect(route('products.index'));

        $product = Product::query()->sole();
        $this->assertDatabaseHas('products', ['code' => 'P-001', 'quantity' => 10]);

        $this->patch(route('products.update', $product), array_merge($this->validProduct(), [
            'code' => 'P-002',
            'quantity' => 15,
        ]))->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'code' => 'P-002', 'quantity' => 15]);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_creation_rejects_invalid_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('products.index'))
            ->post(route('products.store'), ['code' => '', 'quantity' => -1])
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors(['code', 'name', 'category', 'type', 'width', 'length', 'quantity', 'location', 'material', 'wood']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @return array<string, float|int|string> */
    private function validProduct(): array
    {
        return [
            'code' => 'P-001',
            'name' => 'Palete teste',
            'category' => 'Descartável',
            'type' => 'PBR',
            'width' => 1,
            'length' => 1.2,
            'quantity' => 10,
            'location' => 'Barracão 1',
            'material' => 'Madeira',
            'wood' => 'Pinus',
        ];
    }
}
