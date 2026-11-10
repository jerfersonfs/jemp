<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'warehouse_name' => fake()->unique()->words(2, true),
            'warehouse_description' => fake()->sentence(),
        ];
    }
}
