<?php

namespace Tests\Feature\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store – caminho feliz (via JSON / API)
    // -------------------------------------------------------------------------

    public function test_store_cria_produto_com_dados_validos_via_api(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('api.products.store'), $this->dadosValidos());

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Produto criado com sucesso.'])
            ->assertJsonPath('data.product_name', 'Palete PBR Teste');

        $this->assertDatabaseHas('products', [
            'product_name' => 'Palete PBR Teste',
            'category' => 'PBR',
            'material' => 'Madeira',
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);
    }

    public function test_store_gera_sku_automaticamente(): void
    {
        $this->actingAs(User::factory()->create());

        $this->postJson(route('api.products.store'), $this->dadosValidos())
            ->assertStatus(201);

        $produto = Product::query()->sole();

        // SKU gerado segue o padrão CATEGORIA-COMPRIMENTOxLARGURA-SEQ
        $this->assertMatchesRegularExpression('/^PBR-1200X1000-\d{3}$/', $produto->sku);
    }

    // -------------------------------------------------------------------------
    // store – validação
    // -------------------------------------------------------------------------

    public function test_store_rejeita_dados_faltando_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('api.products.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_name',
                'category',
                'material',
                'cost_price',
                'length_mm',
                'width_mm',
            ]);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_rejeita_categoria_invalida(): void
    {
        $this->actingAs(User::factory()->create());

        $dados = array_merge($this->dadosValidos(), ['category' => 'Categoria Inexistente']);

        $response = $this->postJson(route('api.products.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_store_rejeita_preco_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $dados = array_merge($this->dadosValidos(), ['cost_price' => -1]);

        $response = $this->postJson(route('api.products.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_price']);
    }

    public function test_store_rejeita_dimensoes_zero_ou_negativas(): void
    {
        $this->actingAs(User::factory()->create());

        $dados = array_merge($this->dadosValidos(), ['length_mm' => 0, 'width_mm' => -5]);

        $response = $this->postJson(route('api.products.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['length_mm', 'width_mm']);
    }

    public function test_store_rejeita_especificacao_duplicada(): void
    {
        $this->actingAs(User::factory()->create());

        // Cria o primeiro produto
        $this->postJson(route('api.products.store'), $this->dadosValidos())
            ->assertStatus(201);

        // Tenta criar outro com a mesma especificação
        $response = $this->postJson(route('api.products.store'), $this->dadosValidos());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_name']);
    }

    // -------------------------------------------------------------------------
    // update – caminho feliz
    // -------------------------------------------------------------------------

    public function test_update_atualiza_produto_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $produto = Product::factory()->create([
            'category' => 'PBR',
            'product_name' => 'Palete Original',
            'material' => 'Madeira',
            'cost_price' => 25.00,
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);

        $novosDados = [
            'product_name' => 'Palete Atualizado',
            'category' => 'Descartável',
            'material' => 'Plástico',
            'cost_price' => 30.50,
            'length_mm' => 1100,
            'width_mm' => 900,
        ];

        $response = $this->putJson(route('api.products.update', $produto), $novosDados);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Produto atualizado com sucesso.'])
            ->assertJsonPath('data.product_name', 'Palete Atualizado');

        $this->assertDatabaseHas('products', [
            'product_id' => $produto->product_id,
            'product_name' => 'Palete Atualizado',
            'category' => 'Descartável',
            'material' => 'Plástico',
            'length_mm' => 1100,
            'width_mm' => 900,
        ]);
    }

    // -------------------------------------------------------------------------
    // update – validação
    // -------------------------------------------------------------------------

    public function test_update_rejeita_dados_faltando_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $produto = Product::factory()->create();

        $response = $this->putJson(route('api.products.update', $produto), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_name',
                'category',
                'material',
                'cost_price',
                'length_mm',
                'width_mm',
            ]);
    }

    public function test_update_rejeita_especificacao_duplicada_de_outro_produto(): void
    {
        $this->actingAs(User::factory()->create());

        // Produto A já existe com essa especificação
        Product::factory()->create([
            'category' => 'PBR',
            'product_name' => 'Palete PBR Teste',
            'material' => 'Madeira',
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);

        // Produto B que tentará assumir a mesma especificação
        $produtoB = Product::factory()->create([
            'category' => 'Descartável',
            'product_name' => 'Outro Palete',
            'material' => 'Plástico',
            'length_mm' => 800,
            'width_mm' => 600,
        ]);

        $response = $this->putJson(route('api.products.update', $produtoB), $this->dadosValidos());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_name']);
    }

    public function test_update_permite_manter_mesma_especificacao_do_proprio_produto(): void
    {
        $this->actingAs(User::factory()->create());

        $produto = Product::factory()->create([
            'category' => 'PBR',
            'product_name' => 'Palete PBR Teste',
            'material' => 'Madeira',
            'cost_price' => 25.00,
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);

        // Atualiza apenas o preço, mantendo a mesma especificação
        $dados = array_merge($this->dadosValidos(), ['cost_price' => 35.00]);

        $response = $this->putJson(route('api.products.update', $produto), $dados);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'product_id' => $produto->product_id,
            'cost_price' => '35.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function dadosValidos(): array
    {
        return [
            'product_name' => 'Palete PBR Teste',
            'category' => 'PBR',
            'material' => 'Madeira',
            'cost_price' => 25.00,
            'length_mm' => 1200,
            'width_mm' => 1000,
        ];
    }

    public function test_store_retorna_erro_quando_transacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        Event::listen('eloquent.creating: '.Product::class, static function (): void {
            throw new \RuntimeException('Falha simulada na criacao do produto.');
        });
        Event::listen('eloquent.updating: '.Product::class, static function (): void {
            throw new \RuntimeException('Falha simulada na atualizacao do produto.');
        });
        Event::listen('eloquent.deleting: '.Product::class, static function (): void {
            throw new \RuntimeException('Falha simulada na exclusao do produto.');
        });

        $response = $this->postJson(route('api.products.store'), $this->dadosValidos());

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao cadastrar produto.');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_update_retorna_erro_quando_atualizacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create([
            'category' => 'PBR',
            'product_name' => 'Produto original',
            'material' => 'Madeira',
            'cost_price' => 25,
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);
        Event::listen('eloquent.updating: '.Product::class, static function (): void {
            throw new \RuntimeException('Falha simulada na atualizacao do produto.');
        });

        $response = $this->putJson(route('api.products.update', $product), [
            'product_name' => 'Produto atualizado',
            'category' => 'PBR',
            'material' => 'Madeira',
            'cost_price' => 30,
            'length_mm' => 1200,
            'width_mm' => 1000,
        ]);

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao atualizar produto.');

        $this->assertDatabaseHas('products', [
            'product_id' => $product->product_id,
            'product_name' => 'Produto original',
        ]);
    }

    public function test_destroy_exclui_produto_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $response = $this->deleteJson(route('api.products.destroy', $product));

        $response->assertOk()
            ->assertJsonPath('message', 'Produto excluído com sucesso.');

        $this->assertDatabaseMissing('products', ['product_id' => $product->product_id]);
    }

    public function test_destroy_retorna_erro_quando_exclusao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        Event::listen('eloquent.deleting: '.Product::class, static function (): void {
            throw new \RuntimeException('Falha simulada na exclusao do produto.');
        });

        $response = $this->deleteJson(route('api.products.destroy', $product));

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao excluir produto.');

        $this->assertModelExists($product);
    }
}
