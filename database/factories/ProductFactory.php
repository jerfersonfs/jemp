<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('###'),
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['Descartável', 'Retornável']),
            'type' => fake()->randomElement(['PBR', 'FP']),
            'width' => 1.00,
            'length' => 1.20,
            'quantity' => fake()->numberBetween(0, 100),
            'location' => fake()->randomElement(['Barracão 1', 'Barracão 2', 'Barracão 3']),
            'material' => 'Madeira',
            'wood' => 'Madeira',
        ];
    }
}
