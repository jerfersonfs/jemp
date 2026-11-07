<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-1 year', 'now');
        $dueDate = fake()->dateTimeBetween($issueDate, '+6 months');

        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => fake()->unique()->numberBetween(1, 99999),
            'invoice_serie' => fake()->numberBetween(1, 10),
            'issue_date' => $issueDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'payment_method' => fake()->randomElement(['Boleto', 'Pix', 'Cartão', 'Dinheiro']),
            'delivery_driver' => fake()->optional()->name(),
            'payment_terms_days' => fake()->randomElement([0, 30, 60, 90]),
            'payment_status' => fake()->randomElement(['A vencer', 'Vencida', 'Paga', 'Cancelada']),
            'payment_date' => null,
            'amount' => fake()->randomFloat(2, 100, 50000),
        ];
    }

    public function paga(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'Paga',
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ]);
    }

    public function aVencer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'A vencer',
            'payment_date' => null,
        ]);
    }
}
