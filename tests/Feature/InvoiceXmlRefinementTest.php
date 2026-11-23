<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceXmlRefinementTest extends TestCase
{
    use SetsUpInvoicePayments;

    public function test_preview_matches_codes_and_warns_about_description(): void
    {
        $this->catalog();
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload()])
            ->assertOk()->assertJsonPath('items.0.product_id', 1)
            ->assertJsonPath('items.0.description_mismatch', false)
            ->assertJsonPath('items.1.product_id', 2)
            ->assertJsonPath('items.1.description_mismatch', true)
            ->assertJsonPath('products.1.inventory.0.balance', 10);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_import_confirms_the_reviewed_due_date_and_amount(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])
            ->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->invoice_id, 'due_date' => '2026-12-13', 'paid_at' => null, 'payment_method' => null, 'installment_count' => 1]);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 8]);
        $this->assertDatabaseHas('inventory', ['product_id' => 2, 'theoretical_balance' => 7]);
        $this->assertDatabaseCount('invoice_items', 2);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertConflict();
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_unknown_code_is_not_resolved_by_old_mapping_and_blocks_all_writes(): void
    {
        $data = $this->catalog();
        Product::query()->whereKey(2)->update(['sku' => '7']);
        DB::table('product_xml_mappings')->insert(['issuer_document' => '99999999000191', 'external_code' => '0007', 'product_id' => 2]);
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload()])
            ->assertOk()->assertJsonPath('items.1.product_id', null);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])
            ->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 10]);
    }

    public function test_disabled_condition_cannot_be_sold_even_with_available_stock(): void
    {
        $data = $this->catalog();
        DB::table('stock_condition_settings')->where('product_condition', 'Novo')->update(['saleable' => false]);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])
            ->assertUnprocessable()->assertJsonValidationErrors('product_condition');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 10]);
    }

    public function test_manual_product_override_is_rejected(): void
    {
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload(), 'items' => [['line_number' => 1, 'product_id' => 2]]])
            ->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_insufficient_stock_rolls_back_entire_import(): void
    {
        $data = $this->catalog();
        DB::table('inventory')->where('product_id', 2)->update(['theoretical_balance' => 1]);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertUnprocessable();
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => 1, 'theoretical_balance' => 10]);
    }
}
