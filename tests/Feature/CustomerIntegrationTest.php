<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_and_delete_customer(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('customers.store'), $this->validCustomer())
            ->assertRedirect(route('customers.index'));

        $customer = Customer::query()->sole();
        $this->assertDatabaseHas('customers', ['document' => '12.345.678/0001-90', 'status' => 'Ativo']);

        $this->patch(route('customers.update', $customer), array_merge($this->validCustomer(), [
            'name' => 'Empresa atualizada',
            'document' => '98.765.432/0001-12',
            'status' => 'Inativo',
        ]))->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Empresa atualizada', 'status' => 'Inativo']);

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_creation_rejects_invalid_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('customers.index'))
            ->post(route('customers.store'), ['name' => '', 'document' => 'invalid'])
            ->assertRedirect(route('customers.index'))
            ->assertSessionHasErrors(['name', 'document', 'city', 'status']);

        $this->assertDatabaseCount('customers', 0);
    }

    /** @return array<string, string> */
    private function validCustomer(): array
    {
        return [
            'name' => 'Empresa teste',
            'document' => '12.345.678/0001-90',
            'city' => 'Itapira - SP',
            'phone' => '(19) 3863-0000',
            'email' => 'contato@example.com',
            'status' => 'Ativo',
        ];
    }
}
