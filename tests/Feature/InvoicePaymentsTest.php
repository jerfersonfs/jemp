<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\SalePayment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InvoicePaymentsTest extends TestCase
{
    use SetsUpInvoicePayments;

    public function test_xml_suggests_the_number_of_installments_without_opening_receivables(): void
    {
        $this->catalog();
        $xml = $this->upload()->getContent();
        $duplicates = '<cobr><dup><dVenc>2026-12-10</dVenc><vDup>50.30</vDup></dup><dup><dVenc>2027-01-10</dVenc><vDup>139.70</vDup></dup></cobr>';
        foreach ([$duplicates, ''] as $replacement) {
            $file = UploadedFile::fake()->createWithContent('nota.xml', preg_replace('~<cobr>.*?</cobr>~s', $replacement, $xml));
            $this->postJson('/api/invoices/import/preview', ['xml_file' => $file])->assertOk()
                ->assertJsonPath('invoice.installment_count', $replacement === '' ? 1 : 2)
                ->assertJsonPath('invoice.due_date', $replacement === '' ? '' : '2026-12-10')
                ->assertJsonPath('invoice.payment_method', 'Boleto')
                ->assertJsonMissingPath('invoice.payments');
        }
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_xml_paid_on_credit_moves_full_value_to_received_even_when_split_three_ways(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', [...$data, 'xml_file' => $this->upload(),
            'due_date' => '2099-01-01', 'installment_count' => 3, 'payment_status' => 'Paga',
            'payment_date' => '2026-11-19', 'payment_method' => 'Cartão de crédito'])->assertCreated();

        $invoice = Invoice::sole();
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->getKey(), 'installment_count' => 3,
            'paid_at' => '2026-11-19', 'payment_method' => 'Cartão de crédito', 'due_date' => '2026-12-13']);
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->getKey(), 'amount' => 190]);
        $this->get('/')->assertOk()->assertViewHas('receivedTotal', 'R$ 190,00')->assertViewHas('receivableTotal', 'R$ 0,00')
            ->assertViewHas('receivables', []);
        $this->get('/consulta-nfe')->assertOk()->assertSee('2 × R$ 63,33 + 1 × R$ 63,34')
            ->assertDontSee('Registrar recebimento integral')->assertDontSee('Adicionar parcela');
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_payment_validation_leaves_invoice_and_stock_together(): void
    {
        $data = $this->catalog();
        foreach ([
            ['payment_status' => 'Paga'], ['installment_count' => 0], ['installment_count' => 19001],
            ['payment_terms_days' => 2147483647], ['payment_date' => '2026-11-13'],
            ['payments' => [['amount_due' => '95.00']]],
        ] as $invalid) {
            $this->postJson('/api/invoices/import', [...$data, ...$invalid, 'xml_file' => $this->upload()])->assertUnprocessable();
        }
        foreach (['payments', 'invoices', 'stock_movements', 'invoice_items'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 10]);
    }

    public function test_unpaid_sale_has_one_due_date_and_one_full_settlement(): void
    {
        $catalog = $this->catalog();
        $this->travelTo(Carbon::parse('2026-11-19 23:59:00', 'America/Sao_Paulo'));
        $this->postJson('/api/invoices', [
            'customer_id' => $catalog['customer_id'], 'invoice_number' => 900, 'invoice_serie' => 1,
            'issue_date' => '2026-11-19', 'items' => [['product_id' => 1, 'quantity' => 180]],
            'payment_status' => 'A vencer', 'payment_terms_days' => 0, 'installment_count' => 3,
        ])->assertCreated();
        $invoice = Invoice::sole();
        $this->get('/')->assertOk()->assertViewHas('receivableTotal', 'R$ 900,00')->assertViewHas('overdueTotal', 'R$ 0,00')
            ->assertViewHas('receivables', fn (array $rows): bool => count($rows) === 1 && $rows[0]['status'] === 'A vencer');
        $this->travelTo(Carbon::parse('2026-11-20 00:01:00', 'America/Sao_Paulo'));
        $this->get('/')->assertOk()->assertViewHas('overdueTotal', 'R$ 900,00');
        $this->get('/consulta-nfe')->assertOk()->assertSee('3 × R$ 300,00')->assertSee('Registrar recebimento integral');

        $url = route('payments.settle', $invoice);
        $receipt = ['paid_at' => '2026-11-20', 'payment_method' => 'Cartão de crédito'];
        $this->postJson($url, $receipt + ['amount' => 300])->assertUnprocessable();
        $this->postJson($url, ['paid_at' => '2026-11-21', 'payment_method' => 'X'])->assertUnprocessable();
        $this->postJson($url, $receipt)->assertOk();
        $this->postJson($url, $receipt)->assertConflict();
        $this->get('/')->assertOk()->assertViewHas('receivedTotal', 'R$ 900,00')->assertViewHas('receivableTotal', 'R$ 0,00')
            ->assertViewHas('overdueTotal', 'R$ 0,00');
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_existing_invoice_status_action_receives_the_whole_sale(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload(), 'installment_count' => 3])->assertCreated();
        $invoice = Invoice::sole();
        $statusUrl = route('api.invoices.update', $invoice);
        $this->patchJson($statusUrl, ['payment_status' => 'Paga', 'payment_method' => 'PIX'])
            ->assertOk()->assertJsonPath('data.payment_status', 'Paga')->assertJsonPath('data.payment_date', '2026-11-19');
        $this->postJson(route('payments.settle', $invoice), ['paid_at' => '2026-11-19', 'payment_method' => 'PIX'])->assertConflict();
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_centavos_are_distributed_without_changing_the_total(): void
    {
        $this->assertSame('2 × R$ 33,33 + 1 × R$ 33,34', SalePayment::describe('100.00', 3));
        $this->assertSame('1 × R$ 33,33 + 2 × R$ 33,34', SalePayment::describe('100.01', 3));
        $this->assertSame('3 × R$ 300,00', SalePayment::describe('900.00', 3));
    }
}
