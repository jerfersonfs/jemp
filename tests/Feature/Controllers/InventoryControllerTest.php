<?php

namespace Tests\Feature\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store – caminho feliz
    // -------------------------------------------------------------------------

    public function test_store_cria_registro_de_inventario_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 50,
        ];

        $response = $this->postJson(route('api.inventories.store'), $dados);

        $response->assertCreated()
            ->assertJsonPath('data.warehouse_id', $armazem->warehouse_id)
            ->assertJsonPath('data.product_id', $produto->product_id)
            ->assertJsonPath('data.theoretical_balance', 50);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 50,
        ]);
    }

    public function test_store_aceita_saldo_zero(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Reforma',
            'theoretical_balance' => 0,
        ];

        $response = $this->postJson(route('api.inventories.store'), $dados);

        $response->assertCreated()
            ->assertJsonPath('data.theoretical_balance', 0);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'theoretical_balance' => 0,
        ]);
    }

    // -------------------------------------------------------------------------
    // store – validação
    // -------------------------------------------------------------------------

    public function test_store_rejeita_dados_faltando_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('api.inventories.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'warehouse_id',
                'product_id',
                'product_condition',
                'theoretical_balance',
            ]);

        $this->assertDatabaseCount('inventory', 0);
    }

    public function test_store_rejeita_condicao_invalida(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'CondicaoInexistente',
            'theoretical_balance' => 10,
        ];

        $response = $this->postJson(route('api.inventories.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_condition']);
    }

    public function test_store_rejeita_saldo_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => -1,
        ];

        $response = $this->postJson(route('api.inventories.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theoretical_balance']);
    }

    // -------------------------------------------------------------------------
    // update – caminho feliz
    // -------------------------------------------------------------------------

    public function test_update_atualiza_inventario_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $inventory = Inventory::factory()->create([
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $novosDados = [
            'product_condition' => 'Reforma',
            'theoretical_balance' => 25,
        ];

        $response = $this->putJson(route('api.inventories.update', $inventory), $novosDados);

        $response->assertOk()
            ->assertJsonPath('data.product_condition', 'Reforma')
            ->assertJsonPath('data.theoretical_balance', 25);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Reforma',
            'theoretical_balance' => 25,
        ]);
    }

    // -------------------------------------------------------------------------
    // update – validação
    // -------------------------------------------------------------------------

    public function test_update_rejeita_condicao_invalida(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $inventory = Inventory::factory()->create([
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $response = $this->putJson(route('api.inventories.update', $inventory), [
            'product_condition' => 'CondicaoInvalida',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_condition']);
    }

    public function test_update_rejeita_saldo_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $inventory = Inventory::factory()->create([
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $response = $this->putJson(route('api.inventories.update', $inventory), [
            'theoretical_balance' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theoretical_balance']);
    }

    public function test_show_localiza_inventario_pela_chave_composta(): void
    {
        $this->actingAs(User::factory()->create());

        $inventory = Inventory::factory()->create([
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $response = $this->getJson(route('api.inventories.show', $inventory));

        $response->assertOk()
            ->assertJsonPath('warehouse_id', $inventory->warehouse_id)
            ->assertJsonPath('product_id', $inventory->product_id)
            ->assertJsonPath('product_condition', 'Novo');
    }

    public function test_destroy_localiza_e_remove_inventario_pela_chave_composta(): void
    {
        $this->actingAs(User::factory()->create());

        $inventory = Inventory::factory()->create([
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $response = $this->deleteJson(route('api.inventories.destroy', $inventory));

        $response->assertNoContent();

        $this->assertDatabaseMissing('inventory', [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'product_condition' => 'Novo',
        ]);
    }

    public function test_store_retorna_erro_quando_criacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        Event::listen('eloquent.creating: '.Inventory::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao criar inventario.');
        });

        $response = $this->postJson(route('api.inventories.store'), [
            'warehouse_id' => $warehouse->warehouse_id,
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao cadastrar inventario.');

        $this->assertDatabaseCount('inventory', 0);
    }

    public function test_update_retorna_erro_quando_atualizacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $inventory = Inventory::factory()->create([
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);
        Event::listen('eloquent.updating: '.Inventory::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao atualizar inventario.');
        });

        $response = $this->putJson(route('api.inventories.update', $inventory), [
            'theoretical_balance' => 20,
        ]);

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao atualizar inventario.');

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);
    }

    public function test_destroy_retorna_erro_quando_exclusao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $inventory = Inventory::factory()->create([
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);
        Event::listen('eloquent.deleting: '.Inventory::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao excluir inventario.');
        });

        $response = $this->deleteJson(route('api.inventories.destroy', $inventory));

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao excluir inventario.');

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'product_condition' => 'Novo',
        ]);
    }

    public function test_remove_stock_reduz_saldo_do_inventario(): void
    {
        $this->actingAs(User::factory()->create());
        $inventory = Inventory::factory()->create([
            'product_condition' => 'Novo',
            'theoretical_balance' => 10,
        ]);

        $response = $this->postJson(route('api.inventories.remove-stock'), [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => (string) $inventory->product_id,
            'quantity' => 3,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Saída de estoque processada com sucesso!')
            ->assertJsonPath('data.theoretical_balance', 7);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 7,
        ]);
    }

    public function test_remove_stock_rejeita_saida_maior_que_o_saldo_sem_alterar_inventario(): void
    {
        $this->actingAs(User::factory()->create());
        $inventory = Inventory::factory()->create([
            'product_condition' => 'Novo',
            'theoretical_balance' => 5,
        ]);

        $response = $this->postJson(route('api.inventories.remove-stock'), [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => (string) $inventory->product_id,
            'quantity' => 6,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error', 'Falha na movimentação de estoque.')
            ->assertJsonPath('details', 'Estoque insuficiente. Saldo atual: 5 | Solicitado: 6');

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 5,
        ]);
    }
}
