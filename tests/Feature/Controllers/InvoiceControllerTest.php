<?php

namespace Tests\Feature\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store – caminho feliz
    // -------------------------------------------------------------------------

    public function test_store_cria_nota_fiscal_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();

        $dados = $this->dadosValidos($cliente->customer_id);

        $response = $this->postJson(route('api.invoices.store'), $dados);

        $response->assertStatus(201);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 1001,
            'invoice_serie' => 1,
            'customer_id' => $cliente->customer_id,
            'payment_method' => 'Boleto',
            'payment_status' => 'A vencer',
        ]);
    }

    public function test_store_cria_nota_fiscal_paga_com_data_de_pagamento(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();

        $dados = array_merge($this->dadosValidos($cliente->customer_id), [
            'payment_status' => 'Paga',
            'payment_date' => '2025-01-15',
        ]);

        $response = $this->postJson(route('api.invoices.store'), $dados);

        $response->assertStatus(201);

        $this->assertDatabaseHas('invoices', [
            'payment_status' => 'Paga',
        ]);

        $invoice = Invoice::query()->where('invoice_number', 1001)->sole();
        $this->assertSame('2025-01-15', $invoice->payment_date->toDateString());
    }

    // -------------------------------------------------------------------------
    // store – validação
    // -------------------------------------------------------------------------

    public function test_store_rejeita_dados_faltando_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('api.invoices.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customer_id',
                'invoice_number',
                'invoice_serie',
                'issue_date',
                'due_date',
                'payment_method',
                'payment_terms_days',
                'amount',
            ]);

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_store_rejeita_cliente_inexistente(): void
    {
        $this->actingAs(User::factory()->create());

        $dados = $this->dadosValidos(99999);

        $response = $this->postJson(route('api.invoices.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_store_rejeita_data_vencimento_anterior_a_emissao(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();

        $dados = array_merge($this->dadosValidos($cliente->customer_id), [
            'issue_date' => '2025-06-01',
            'due_date' => '2025-05-01',
        ]);

        $response = $this->postJson(route('api.invoices.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_store_rejeita_status_invalido(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();

        $dados = array_merge($this->dadosValidos($cliente->customer_id), [
            'payment_status' => 'StatusInexistente',
        ]);

        $response = $this->postJson(route('api.invoices.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_status']);
    }

    public function test_store_rejeita_valor_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();

        $dados = array_merge($this->dadosValidos($cliente->customer_id), ['amount' => -100]);

        $response = $this->postJson(route('api.invoices.store'), $dados);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    // -------------------------------------------------------------------------
    // update – caminho feliz
    // -------------------------------------------------------------------------

    public function test_update_atualiza_nota_fiscal_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();
        $invoice = Invoice::factory()->aVencer()->create(['customer_id' => $cliente->customer_id]);

        $novosDados = [
            'payment_status' => 'Paga',
            'payment_date' => '2025-02-20',
        ];

        $response = $this->patchJson(route('api.invoices.update', $invoice), $novosDados);

        $response->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'invoice_id' => $invoice->invoice_id,
            'payment_status' => 'Paga',
        ]);
        $this->assertSame('2025-02-20', $invoice->refresh()->payment_date->toDateString());
    }

    public function test_update_atualiza_metodo_de_pagamento(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();
        $invoice = Invoice::factory()->aVencer()->create(['customer_id' => $cliente->customer_id]);

        $response = $this->patchJson(route('api.invoices.update', $invoice), [
            'payment_method' => 'Pix',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'invoice_id' => $invoice->invoice_id,
            'payment_method' => 'Pix',
        ]);
    }

    // -------------------------------------------------------------------------
    // update – validação
    // -------------------------------------------------------------------------

    public function test_update_rejeita_status_invalido(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();
        $invoice = Invoice::factory()->aVencer()->create(['customer_id' => $cliente->customer_id]);

        $response = $this->patchJson(route('api.invoices.update', $invoice), [
            'payment_status' => 'StatusInvalido',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_status']);
    }

    public function test_update_rejeita_data_vencimento_anterior_a_emissao(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Customer::factory()->create();
        $invoice = Invoice::factory()->aVencer()->create([
            'customer_id' => $cliente->customer_id,
            'issue_date' => '2025-06-01',
            'due_date' => '2025-07-01',
        ]);

        $response = $this->patchJson(route('api.invoices.update', $invoice), [
            'issue_date' => '2025-08-01',
            'due_date' => '2025-07-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function dadosValidos(int $customerId): array
    {
        return [
            'customer_id' => $customerId,
            'invoice_number' => 1001,
            'invoice_serie' => 1,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-02-01',
            'payment_method' => 'Boleto',
            'delivery_driver' => 'João Motorista',
            'payment_terms_days' => 30,
            'payment_status' => 'A vencer',
            'amount' => 1500.00,
        ];
    }

    public function test_import_xml_cria_nota_e_atualiza_estoque_com_sucesso(): void
    {
        $customer = Customer::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $xml = '<?xml version="1.0"?><root><NFe><infNFe><ide><nNF>7001</nNF><serie>1</serie><dhEmi>2026-11-01T12:00:00-03:00</dhEmi></ide><total><ICMSTot><vNF>250.50</vNF></ICMSTot></total><det><prod><cProd>'.$product->product_id.'</cProd><qCom>3</qCom></prod></det></infNFe></NFe></root>';
        $file = UploadedFile::fake()->createWithContent('nota.xml', $xml);

        $response = $this->post('/api/invoices/import', [
            'xml_file' => $file,
            'warehouse_id' => $warehouse->warehouse_id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'NFe importada e estoque atualizado com sucesso!')
            ->assertJsonPath('data.invoice_number', '7001');

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 7001,
            'customer_id' => $customer->customer_id,
        ]);
        $this->assertDatabaseHas('inventory', [
            'warehouse_id' => $warehouse->warehouse_id,
            'product_id' => $product->product_id,
            'product_condition' => 'Novo',
            'theoretical_balance' => 3,
        ]);
    }

    public function test_import_xml_retorna_erro_para_xml_sem_nfe(): void
    {
        $warehouse = Warehouse::factory()->create();
        $file = UploadedFile::fake()->createWithContent('nota.xml', '<root><NFe></NFe></root>');

        $response = $this->post('/api/invoices/import', [
            'xml_file' => $file,
            'warehouse_id' => $warehouse->warehouse_id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error', 'Falha ao importar a Nota Fiscal.')
            ->assertJsonPath('details', 'Ficheiro XML inválido ou não é uma NFe brasileira padrão.');

        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('inventory', 0);
    }
}
