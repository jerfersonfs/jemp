<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoicePaymentMigrationTest extends TestCase
{
    use SetsUpInvoicePayments;

    protected function migrateOnSetup(): bool
    {
        return false;
    }

    public function test_historical_sales_keep_their_own_terms_and_receipt_after_customer_terms_are_removed(): void
    {
        $customer = DB::table('customers')->insertGetId([
            'customer_name' => 'Cliente histórico', 'customer_document' => '99999999999', 'customer_payment_terms' => '60 dias',
        ]);
        foreach (['Paga', 'A vencer', 'Cancelada'] as $index => $status) {
            DB::table('invoices')->insert(['invoice_number' => $index + 1, 'invoice_serie' => 1, 'customer_id' => $customer,
                'issue_date' => '2026-11-01', 'due_date' => '2026-11-10', 'payment_terms_days' => 9, 'amount' => '40.25',
                'payment_status' => $status, 'payment_date' => $index === 0 ? '2026-11-09' : null, 'payment_method' => 'PIX']);
        }
        $this->migratePayments();
        $this->migratePayments();

        $this->assertFalse(Schema::hasColumn('customers', 'customer_payment_terms'));
        foreach (['payment_status', 'payment_method', 'payment_terms_days', 'due_date', 'payment_date'] as $field) {
            $this->assertFalse(Schema::hasColumn('invoices', $field));
        }
        $this->assertDatabaseCount('payments', 3);
        $this->assertDatabaseHas('payments', ['invoice_id' => 1, 'installment_count' => 1, 'paid_at' => '2026-11-09', 'payment_method' => 'PIX']);
        $this->assertDatabaseHas('payments', ['invoice_id' => 2, 'payment_terms_days' => 9, 'due_date' => '2026-11-10', 'payment_method' => 'PIX', 'paid_at' => null]);
        $this->assertDatabaseHas('invoices', ['invoice_id' => 3, 'is_cancelled' => true, 'amount' => 40.25]);
        $this->actingAs(User::factory()->create());
        $this->postJson(route('payments.settle', 3), ['paid_at' => '2026-11-19', 'payment_method' => 'PIX'])->assertConflict();
        $this->get('/')->assertOk()->assertViewHas('receivedTotal', 'R$ 40,25')
            ->assertViewHas('receivableTotal', 'R$ 40,25')->assertViewHas('overdueTotal', 'R$ 40,25');
    }
}
