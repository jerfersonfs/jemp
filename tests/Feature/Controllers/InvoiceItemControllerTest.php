<?php

namespace Tests\Feature\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InvoiceItemControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store – caminho feliz
    // -------------------------------------------------------------------------

    public function test_store_cria_item_de_nota_fiscal_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = $this->criarInvoice();
        $produto = Product::factory()->create();

        $dados = [
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 10,
            'unit_price' => 25.50,
        ];

        $response = $this->postJson(route('api.invoice-items.store'), $dados);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Item de nota fiscal criado com sucesso.'])
            ->assertJsonPath('data.quantity', 10);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 10,
        ]);
    }

    // -------------------------------------------------------------------------
    // store – validação
    // -------------------------------------------------------------------------

    public function test_store_rejeita_dados_faltando_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('api.invoice-items.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'invoice_id',
                'product_id',
                'quantity',
                'unit_price',
            ]);

        $this->assertDatabaseCount('invoice_items', 0);
    }

    public function test_store_rejeita_quantidade_menor_que_um(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = $this->criarInvoice();
        $produto = Product::factory()->create();

        $dados = [
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 0,
            'unit_price' => 10.00,
        ];

        $response = $this->postJson(route('api.invoice-items.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_store_rejeita_preco_unitario_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = $this->criarInvoice();
        $produto = Product::factory()->create();

        $dados = [
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 5,
            'unit_price' => -1.00,
        ];

        $response = $this->postJson(route('api.invoice-items.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    }

    // -------------------------------------------------------------------------
    // update – caminho feliz
    // -------------------------------------------------------------------------

    public function test_update_atualiza_item_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = $this->criarInvoice();
        $produto = Product::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 5,
            'unit_price' => 10.00,
        ]);

        $novosDados = [
            'quantity' => 20,
            'unit_price' => 15.75,
        ];

        $response = $this->putJson(route('api.invoice-items.update', $item), $novosDados);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Item de nota fiscal atualizado com sucesso.'])
            ->assertJsonPath('data.quantity', 20);

        $this->assertDatabaseHas('invoice_items', [
            'item_id' => $item->item_id,
            'quantity' => 20,
        ]);
    }

    // -------------------------------------------------------------------------
    // update – validação
    // -------------------------------------------------------------------------

    public function test_update_rejeita_quantidade_menor_que_um(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = $this->criarInvoice();
        $produto = Product::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 5,
            'unit_price' => 10.00,
        ]);

        $response = $this->putJson(route('api.invoice-items.update', $item), [
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_update_rejeita_preco_unitario_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = $this->criarInvoice();
        $produto = Product::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $produto->product_id,
            'quantity' => 5,
            'unit_price' => 10.00,
        ]);

        $response = $this->putJson(route('api.invoice-items.update', $item), [
            'unit_price' => -5.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function criarInvoice(): Invoice
    {
        $cliente = Customer::factory()->create();

        return Invoice::factory()->create([
            'customer_id' => $cliente->customer_id,
            'invoice_number' => 2001,
            'invoice_serie' => 1,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-02-01',
            'payment_method' => 'Boleto',
            'payment_terms_days' => 30,
            'payment_status' => 'A vencer',
            'amount' => 500.00,
        ]);
    }

    public function test_store_retorna_erro_quando_criacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $invoice = $this->criarInvoice();
        $product = Product::factory()->create();
        Event::listen('eloquent.creating: '.InvoiceItem::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao criar item.');
        });

        $response = $this->postJson(route('api.invoice-items.store'), [
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $product->product_id,
            'quantity' => 3,
            'unit_price' => 10,
        ]);

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao cadastrar item da nota fiscal.');

        $this->assertDatabaseCount('invoice_items', 0);
    }

    public function test_update_retorna_erro_quando_atualizacao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $invoice = $this->criarInvoice();
        $product = Product::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $product->product_id,
            'quantity' => 5,
            'unit_price' => 10,
        ]);
        Event::listen('eloquent.updating: '.InvoiceItem::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao atualizar item.');
        });

        $response = $this->putJson(route('api.invoice-items.update', $item), [
            'quantity' => 20,
            'unit_price' => 15,
        ]);

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao atualizar item da nota fiscal.');

        $this->assertDatabaseHas('invoice_items', [
            'item_id' => $item->item_id,
            'quantity' => 5,
            'unit_price' => 10,
        ]);
    }

    public function test_destroy_exclui_item_com_sucesso(): void
    {
        $this->actingAs(User::factory()->create());
        $invoice = $this->criarInvoice();
        $product = Product::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $product->product_id,
        ]);

        $response = $this->deleteJson(route('api.invoice-items.destroy', $item));

        $response->assertOk()
            ->assertJsonPath('message', 'Item de nota fiscal excluído com sucesso.');

        $this->assertDatabaseMissing('invoice_items', ['item_id' => $item->item_id]);
    }

    public function test_destroy_retorna_erro_quando_exclusao_lanca_excecao(): void
    {
        $this->actingAs(User::factory()->create());
        $invoice = $this->criarInvoice();
        $product = Product::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $product->product_id,
        ]);
        Event::listen('eloquent.deleting: '.InvoiceItem::class, static function (): void {
            throw new \RuntimeException('Falha simulada ao excluir item.');
        });

        $response = $this->deleteJson(route('api.invoice-items.destroy', $item));

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Falha ao excluir item da nota fiscal.');

        $this->assertModelExists($item);
    }
}
