<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'product_condition' => fake()->randomElement(['Novo', 'Reforma', 'Descarte']),
            'theoretical_balance' => fake()->numberBetween(0, 500),
        ];
    }
}
