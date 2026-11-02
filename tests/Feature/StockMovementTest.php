<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;

class StockMovementTest extends TestCase
{
    // Esta linha garante que o teste não vai sujar o seu banco de dados real
    use DatabaseTransactions;

    public function test_entrada_de_estoque_soma_saldo_corretamente()
    {
        // 1. Pegamos os primeiros registros do seu banco para o teste
        $user = User::first();
        $warehouse = Warehouse::first();
        $product = Product::first();

        // Trava de segurança caso o banco esteja vazio
        if (!$user || !$warehouse || !$product) {
            $this->markTestSkipped('Cadastre pelo menos 1 Usuário, 1 Armazém e 1 Produto no banco para rodar este teste.');
        }

        // 2. Montamos o JSON que o Frontend enviaria
        // Atenção: Ajuste as chaves ($warehouse->id ou $warehouse->warehouse_id)
        // de acordo com o nome exato da sua Primary Key no model.
        $payload = [
            'origin_warehouse_id' => null,
            'dest_warehouse_id' => $warehouse->warehouse_id,
            'product_id' => $product->product_id,
            'user_id' => $user->id,
            'product_condition' => 'Novo',
            'movement_type' => 'Entrada',
            'quantity' => 20,
            'movement_date' => now()->format('Y-m-d'),
            'is_fiscal' => false,
        ];

        // Dispara a requisição POST simulando que o usuário está logado
        $response = $this->actingAs($user)->postJson('/movimentacoes', $payload);

        // Verifica se a API aprovou a movimentação (Código HTTP 201 - Created)
        $response->assertStatus(201);

        // Verifica se funcionou
        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $warehouse->warehouse_id,
            'product_id' => $product->product_id,
            'theoretical_balance' => 20
        ]);
    }
}
