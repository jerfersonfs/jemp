<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'origin_warehouse_id' => null,
            'dest_warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'invoice_id' => null,
            'product_condition' => fake()->randomElement(['Novo', 'Reforma', 'Descarte']),
            'movement_type' => 'Entrada',
            'quantity' => fake()->numberBetween(1, 100),
            'movement_date' => now(),
            'movement_justification' => fake()->optional()->sentence(),
            'is_fiscal' => false,
        ];
    }
}
