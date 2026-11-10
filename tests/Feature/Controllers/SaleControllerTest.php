<?php

namespace Tests\Feature\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_exibe_relatorio_quando_stored_procedure_responde(): void
    {
        $this->actingAs(User::factory()->create());
        DB::shouldReceive('select')->once()->andReturn([]);

        $response = $this->get(route('sales.index'));

        $response->assertOk()
            ->assertViewHas('salesReport', [])
            ->assertViewHas('salesReportError', null);
    }

    public function test_index_exibe_mensagem_quando_stored_procedure_lanca_query_exception(): void
    {
        $this->actingAs(User::factory()->create());
        $exception = new QueryException(
            'sqlite',
            'CALL sp_relatorio_vendas_periodo(?, ?)',
            [],
            new \PDOException('Falha simulada na stored procedure.'),
        );
        DB::shouldReceive('select')->once()->andThrow($exception);

        $response = $this->get(route('sales.index'));

        $response->assertOk()
            ->assertViewHas('salesReport', [])
            ->assertViewHas('salesReportError', 'Não foi possível carregar o relatório. Verifique se a stored procedure está aplicada no banco e se a conexão está ativa.');
    }

    public function test_customer_store_cria_cliente_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $data = [
            'customer_name' => 'Cliente teste',
            'customer_document' => '12345678000190',
            'customer_contact' => '(11) 99999-0000',
            'customer_email' => 'cliente@example.com',
            'customer_payment_terms' => '30 dias',
        ];
        $response = $this->postJson(route('api.customers.store'), $data);
        $response->assertCreated()->assertJsonPath('data.customer_name', 'Cliente teste');
        $this->assertDatabaseHas('customers', ['customer_document' => '12345678000190']);
    }

    public function test_customer_store_retorna_erro_quando_criacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        Event::listen('eloquent.creating: '.Customer::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao criar cliente.');
        });
        $response = $this->postJson(route('api.customers.store'), [
            'customer_name' => 'Cliente teste',
            'customer_document' => '12345678000190',
            'customer_contact' => '(11) 99999-0000',
            'customer_email' => 'cliente@example.com',
            'customer_payment_terms' => '30 dias',
        ]);
        $response->assertInternalServerError()->assertJsonPath('message', 'Falha ao cadastrar cliente.');
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_customer_update_atualiza_cliente_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::factory()->create();
        $response = $this->putJson(route('api.customers.update', $customer), [
            'customer_name' => 'Cliente atualizado',
            'customer_document' => '98765432000112',
            'customer_contact' => '(11) 99999-0000',
            'customer_email' => 'cliente@example.com',
            'customer_payment_terms' => '30 dias',
        ]);
        $response->assertOk()->assertJsonPath('data.customer_name', 'Cliente atualizado');
        $this->assertDatabaseHas('customers', [
            'customer_id' => $customer->customer_id,
            'customer_name' => 'Cliente atualizado',
        ]);
    }

    public function test_customer_update_retorna_erro_quando_atualizacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::factory()->create();
        Event::listen('eloquent.updating: '.Customer::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao atualizar cliente.');
        });
        $response = $this->putJson(route('api.customers.update', $customer), [
            'customer_name' => 'Cliente atualizado',
            'customer_document' => '98765432000112',
            'customer_contact' => '(11) 99999-0000',
            'customer_email' => 'cliente@example.com',
            'customer_payment_terms' => '30 dias',
        ]);
        $response->assertInternalServerError()->assertJsonPath('message', 'Falha ao atualizar cliente.');
        $this->assertDatabaseHas('customers', [
            'customer_id' => $customer->customer_id,
            'customer_name' => $customer->customer_name,
        ]);
    }

    public function test_customer_destroy_remove_cliente_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::factory()->create();
        $response = $this->deleteJson(route('api.customers.destroy', $customer));
        $response->assertOk()->assertJsonPath('message', 'Cliente excluído com sucesso.');
        $this->assertDatabaseMissing('customers', ['customer_id' => $customer->customer_id]);
    }

    public function test_customer_destroy_retorna_erro_quando_exclusao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::factory()->create();
        Event::listen('eloquent.deleting: '.Customer::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao excluir cliente.');
        });
        $response = $this->deleteJson(route('api.customers.destroy', $customer));
        $response->assertInternalServerError()->assertJsonPath('message', 'Falha ao excluir cliente.');
        $this->assertModelExists($customer);
    }

    public function test_warehouse_store_cria_armazem_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $data = ['warehouse_name' => 'Armazem teste', 'warehouse_description' => 'Descricao do armazem.'];
        $response = $this->postJson(route('api.warehouses.store'), $data);
        $response->assertCreated()->assertJsonPath('data.warehouse_name', 'Armazem teste');
        $this->assertDatabaseHas('warehouses', ['warehouse_name' => 'Armazem teste']);
    }

    public function test_warehouse_store_retorna_erro_quando_criacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        Event::listen('eloquent.creating: '.Warehouse::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao criar armazem.');
        });
        $data = ['warehouse_name' => 'Armazem teste', 'warehouse_description' => 'Descricao do armazem.'];
        $response = $this->postJson(route('api.warehouses.store'), $data);
        $response->assertInternalServerError()->assertJsonPath('message', 'Falha ao cadastrar armazem.');
        $this->assertDatabaseCount('warehouses', 0);
    }

    public function test_warehouse_update_atualiza_armazem_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouse = Warehouse::factory()->create();
        $response = $this->putJson(route('api.warehouses.update', $warehouse), [
            'warehouse_name' => 'Armazem atualizado',
            'warehouse_description' => 'Descricao atualizada.',
        ]);
        $response->assertOk()->assertJsonPath('data.warehouse_name', 'Armazem atualizado');
        $this->assertDatabaseHas('warehouses', [
            'warehouse_id' => $warehouse->warehouse_id,
            'warehouse_name' => 'Armazem atualizado',
        ]);
    }

    public function test_warehouse_update_retorna_erro_quando_atualizacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouse = Warehouse::factory()->create();
        Event::listen('eloquent.updating: '.Warehouse::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao atualizar armazem.');
        });
        $response = $this->putJson(route('api.warehouses.update', $warehouse), [
            'warehouse_name' => 'Armazem atualizado',
            'warehouse_description' => 'Descricao atualizada.',
        ]);
        $response->assertInternalServerError()->assertJsonPath('message', 'Falha ao atualizar armazem.');
        $this->assertDatabaseHas('warehouses', [
            'warehouse_id' => $warehouse->warehouse_id,
            'warehouse_name' => $warehouse->warehouse_name,
        ]);
    }

    public function test_warehouse_destroy_remove_armazem_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouse = Warehouse::factory()->create();
        $response = $this->deleteJson(route('api.warehouses.destroy', $warehouse));
        $response->assertOk()->assertJsonPath('message', 'Armazém excluído com sucesso.');
        $this->assertDatabaseMissing('warehouses', ['warehouse_id' => $warehouse->warehouse_id]);
    }

    public function test_warehouse_destroy_retorna_erro_quando_exclusao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouse = Warehouse::factory()->create();
        Event::listen('eloquent.deleting: '.Warehouse::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao excluir armazem.');
        });
        $response = $this->deleteJson(route('api.warehouses.destroy', $warehouse));
        $response->assertInternalServerError()->assertJsonPath('message', 'Falha ao excluir armazem.');
        $this->assertModelExists($warehouse);
    }
}
