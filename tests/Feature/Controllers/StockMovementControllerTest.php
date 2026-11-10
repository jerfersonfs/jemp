<?php

namespace Tests\Feature\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store – caminho feliz: Entrada
    // -------------------------------------------------------------------------

    public function test_store_registra_entrada_e_incrementa_saldo(): void
    {
        $user = User::factory()->create();
        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'dest_warehouse_id' => $armazem->warehouse_id,
            'origin_warehouse_id' => null,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'Entrada',
            'quantity' => 30,
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'movement_justification' => 'Recebimento de fornecedor',
            'is_fiscal' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Movimentação registrada e saldo atualizado com sucesso.']);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 30,
        ]);
    }

    // -------------------------------------------------------------------------
    // store – caminho feliz: Saída
    // -------------------------------------------------------------------------

    public function test_store_registra_saida_e_decrementa_saldo(): void
    {
        $user = User::factory()->create();
        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        // Cria saldo inicial
        Inventory::factory()->create([
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 50,
        ]);

        $dados = [
            'origin_warehouse_id' => $armazem->warehouse_id,
            'dest_warehouse_id' => null,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'Saída',
            'quantity' => 20,
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'movement_justification' => 'Venda ao cliente',
            'is_fiscal' => true,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(201);

        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'theoretical_balance' => 30,
        ]);
    }

    // -------------------------------------------------------------------------
    // store – caminho feliz: Transferência
    // -------------------------------------------------------------------------

    public function test_store_registra_transferencia_entre_armazens(): void
    {
        $user = User::factory()->create();
        $origem = Warehouse::factory()->create();
        $destino = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        // Saldo no armazém de origem
        Inventory::factory()->create([
            'warehouse_id' => $origem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 100,
        ]);

        $dados = [
            'origin_warehouse_id' => $origem->warehouse_id,
            'dest_warehouse_id' => $destino->warehouse_id,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'Transferência',
            'quantity' => 40,
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'movement_justification' => 'Redistribuição de estoque',
            'is_fiscal' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(201);

        // Origem deve ter diminuído
        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $origem->warehouse_id,
            'product_id' => $produto->product_id,
            'theoretical_balance' => 60,
        ]);

        // Destino deve ter aumentado
        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $destino->warehouse_id,
            'product_id' => $produto->product_id,
            'theoretical_balance' => 40,
        ]);
    }

    // -------------------------------------------------------------------------
    // store – validação
    // -------------------------------------------------------------------------

    public function test_store_rejeita_dados_faltando_campos_obrigatorios(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_id',
                'user_id',
                'product_condition',
                'movement_type',
                'quantity',
                'movement_date',
                'is_fiscal',
            ]);
    }

    public function test_store_rejeita_tipo_de_movimentacao_invalido(): void
    {
        $user = User::factory()->create();
        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'dest_warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'TipoInvalido',
            'quantity' => 10,
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'is_fiscal' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['movement_type']);
    }

    public function test_store_rejeita_condicao_de_produto_invalida(): void
    {
        $user = User::factory()->create();
        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'dest_warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'CondicaoInvalida',
            'movement_type' => 'Entrada',
            'quantity' => 10,
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'is_fiscal' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_condition']);
    }

    public function test_store_rejeita_quantidade_menor_que_um(): void
    {
        $user = User::factory()->create();
        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        $dados = [
            'dest_warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'Entrada',
            'quantity' => 0,
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'is_fiscal' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_store_rejeita_saida_com_saldo_insuficiente(): void
    {
        $user = User::factory()->create();
        $armazem = Warehouse::factory()->create();
        $produto = Product::factory()->create();

        // Saldo inicial de apenas 5 unidades
        Inventory::factory()->create([
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 5,
        ]);

        $dados = [
            'origin_warehouse_id' => $armazem->warehouse_id,
            'dest_warehouse_id' => null,
            'product_id' => $produto->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'Saída',
            'quantity' => 100, // Mais do que o saldo disponível
            'movement_date' => now()->format('Y-m-d H:i:s'),
            'is_fiscal' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('api.stock-movements.store'), $dados);

        $response->assertStatus(422);

        // Saldo não deve ter sido alterado
        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $armazem->warehouse_id,
            'product_id' => $produto->product_id,
            'theoretical_balance' => 5,
        ]);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
