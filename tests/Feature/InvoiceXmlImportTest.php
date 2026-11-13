<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceXmlImportTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-13 10:00:00', 'America/Sao_Paulo'));
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_import_and_preview_require_authentication(): void
    {
        $this->postJson('/api/invoices/import/preview')->assertUnauthorized();
        $this->postJson('/api/invoices/import')->assertUnauthorized();
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_preview_keeps_external_codes_and_writes_nothing(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $unrelated = new Product(['sku' => 'OUTRO-CADASTRO', 'product_name' => 'Produto sem relação com o XML',
            'category' => 'PBR', 'material' => 'Madeira', 'cost_price' => 5, 'length_mm' => 1200, 'width_mm' => 1000]);
        $unrelated->product_id = 7;
        $unrelated->save();
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload()])
            ->assertOk()->assertJsonPath('items.0.external_code', 'EXT-PAL-001')
            ->assertJsonPath('items.1.external_code', '0007')
            ->assertJsonPath('items.1.product_id', null)->assertJsonPath('invoice.amount', '190.00');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('product_xml_mappings', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 10]);
    }

    public function test_confirm_imports_xml_prices_reduces_stock_and_locks_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'amount' => 190, 'payment_status' => 'A vencer', 'issuer_document' => '99999999000191']);
        $this->assertNotNull($invoice->stock_posted_at);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 8]);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][1]['product_id'], 'theoretical_balance' => 17]);
        $this->assertDatabaseHas('invoice_items', ['product_id' => $data['items'][0]['product_id'], 'external_code' => 'EXT-PAL-001', 'unit_price' => 50]);
        $this->assertDatabaseHas('products', ['product_id' => $data['items'][0]['product_id'], 'cost_price' => 5]);
        $this->assertDatabaseHas('stock_movements', ['invoice_id' => $invoice->invoice_id, 'user_id' => $user->id, 'movement_type' => 'Saída', 'is_fiscal' => 1]);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('product_xml_mappings', ['external_code' => '0007', 'product_id' => $data['items'][1]['product_id']]);
        $item = $invoice->items()->firstOrFail();
        $this->postJson('/api/invoice-items', ['invoice_id' => $invoice->invoice_id, 'product_id' => $item->product_id, 'quantity' => 1])->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
        $this->patchJson('/api/invoice-items/'.$item->item_id, ['quantity' => 9])->assertUnprocessable();
        $this->deleteJson('/api/invoice-items/'.$item->item_id)->assertUnprocessable();
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['invoice_number' => 12])->assertUnprocessable();
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => 'Paga'])->assertOk()
            ->assertJsonPath('data.payment_date', '2026-11-13');
        $this->deleteJson('/api/invoices/'.$invoice->invoice_id)->assertUnprocessable();
        $this->get('/consulta-nfe')->assertOk()->assertSee('Importar XML de venda')->assertSee('EXT-PAL-001')->assertSee('Itens bloqueados');
    }

    public function test_duplicate_xml_returns_409_without_another_stock_change(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertConflict()->assertJsonPath('message', 'Esta NF-e já foi importada. Nenhuma baixa adicional foi feita.');
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 8]);
    }

    public function test_insufficient_second_product_rolls_back_every_change(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        DB::table('inventory')->where('product_id', $data['items'][1]['product_id'])->update(['theoretical_balance' => 2]);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('invoice_items', 0);
        $this->assertDatabaseCount('product_xml_mappings', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 10]);
    }

    public function test_multiple_lines_for_one_product_use_the_combined_quantity(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        DB::table('inventory')->where('product_id', $data['items'][0]['product_id'])->update(['theoretical_balance' => 4]);
        $data['items'][1]['product_id'] = $data['items'][0]['product_id'];
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 4]);
    }

    public function test_suggestions_are_scoped_to_issuer_and_same_number_can_have_two_issuers(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $next = $this->changedDocument(900003, '99999999000191');
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload($next)])
            ->assertOk()->assertJsonPath('items.0.product_id', $data['items'][0]['product_id']);
        $otherIssuer = $this->changedDocument(900001, '88888888000191');
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload($otherIssuer)])
            ->assertOk()->assertJsonPath('items.0.product_id', null);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload($otherIssuer)])->assertCreated();
        $this->assertDatabaseCount('invoices', 2);
        $this->assertDatabaseCount('product_xml_mappings', 4);
    }

    public function test_wrong_customer_and_incomplete_mapping_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        Customer::query()->whereKey($data['customer_id'])->update(['customer_document' => '22222222000191']);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertUnprocessable()->assertJsonValidationErrors('customer_id');
        Customer::query()->whereKey($data['customer_id'])->update(['customer_document' => '11111111000191']);
        array_pop($data['items']);
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_unsupported_fractional_values_totals_and_external_entities_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $contents = file_get_contents(base_path('tests/Fixtures/nfe-venda-ficticia.xml'));
        foreach ([
            str_replace('<qCom>2.0000</qCom>', '<qCom>2.5000</qCom>', $contents),
            str_replace('<vNF>190.00</vNF>', '<vNF>200.00</vNF>', $contents),
            str_replace('<vUnCom>50.0000000000</vUnCom>', '<vUnCom>50.001</vUnCom>', $contents),
            str_replace('<uCom>UN</uCom>', '<uCom>KG</uCom>', $contents),
            '<!DOCTYPE NFe [<!ENTITY xxe SYSTEM "file:///C:/Windows/win.ini">]>'.$contents,
            '<invalid>',
        ] as $invalid) {
            $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload($invalid)])->assertUnprocessable()->assertJsonValidationErrors('xml_file');
        }
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_xml_with_namespace_prefix_or_processed_envelope_can_be_reviewed(): void
    {
        $this->actingAs(User::factory()->create());
        $this->catalog();
        $contents = file_get_contents(base_path('tests/Fixtures/nfe-venda-ficticia.xml'));
        $withoutDeclaration = preg_replace('/<\?xml[^?]+\?>/', '', $contents);
        $wrapped = '<nfeProc xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">'.$withoutDeclaration.'</nfeProc>';
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload($wrapped)])->assertOk()->assertJsonPath('items.0.line_number', 1);
        $prefixed = preg_replace('/(<\/?)([A-Za-z][\w]*)/', '$1n:$2', $contents);
        $prefixed = str_replace('xmlns="http://www.portalfiscal.inf.br/nfe"', 'xmlns:n="http://www.portalfiscal.inf.br/nfe"', $prefixed);
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload($prefixed)])->assertOk()->assertJsonPath('invoice.amount', '190.00');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_manual_invoice_items_still_work_without_automatic_stock_deduction(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $response = $this->postJson('/api/invoices', ['invoice_number' => 42, 'invoice_serie' => 1,
            'customer_id' => $data['customer_id'], 'issue_date' => '2026-11-13', 'due_date' => '2026-12-13',
            'payment_method' => 'Boleto', 'payment_terms_days' => 30])->assertCreated();
        $invoice = Invoice::query()->sole();
        $created = $this->postJson('/api/invoice-items', ['invoice_id' => $invoice->invoice_id,
            'product_id' => $data['items'][0]['product_id'], 'quantity' => 2])->assertCreated();
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'amount' => 10]);
        $this->patchJson('/api/invoice-items/'.$created->json('data.item_id'), ['quantity' => 3])->assertOk();
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'amount' => 15]);
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['due_date' => '2026-12-20'])
            ->assertOk()->assertJsonPath('data.due_date', '2026-12-20');
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 10]);
    }

    public function test_legacy_invoice_without_issuer_blocks_ambiguous_import(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        Invoice::query()->create(['invoice_number' => 900001, 'invoice_serie' => 99, 'customer_id' => $data['customer_id'],
            'issue_date' => '2026-11-13', 'due_date' => '2026-12-13', 'payment_method' => 'Boleto',
            'payment_terms_days' => 30, 'payment_status' => 'A vencer', 'amount' => 0]);
        $this->postJson('/api/invoices/import/preview', ['xml_file' => $this->upload()])->assertConflict();
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_manual_exit_entrypoints_are_disabled_and_sidebar_has_no_exit_link(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = $this->catalog();
        $this->get('/saidas')->assertRedirect(route('sales.index'));
        $this->postJson('/saidas', [])->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
        $this->postJson('/api/inventories/remove-stock', [])->assertUnprocessable();
        $this->postJson('/api/stock-movements', [
            'product_id' => $data['items'][0]['product_id'], 'user_id' => $user->id,
            'movement_type' => 'Saída', 'product_condition' => 'Novo', 'quantity' => 1,
            'movement_date' => '2026-11-13', 'is_fiscal' => false,
        ])->assertUnprocessable()->assertJsonValidationErrors('movement_type');
        $this->get('/consulta-nfe')->assertOk()->assertDontSee('href="'.route('exits.index').'"', false);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_entries_and_internal_transfers_keep_their_existing_stock_effects(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $productId = $data['items'][0]['product_id'];
        $destination = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Destino teste']);
        $this->post('/entradas', ['date' => '2026-11-13T10:00', 'product_id' => $productId,
            'product_condition' => 'Novo', 'quantity' => 2, 'dest_warehouse_id' => $data['warehouse_id'],
            'is_fiscal' => false])->assertRedirect(route('entrance.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory', ['product_id' => $productId, 'warehouse_id' => $data['warehouse_id'], 'theoretical_balance' => 12]);
        $this->post('/internas', ['date' => '2026-11-13T10:00', 'product_id' => $productId,
            'product_condition' => 'Novo', 'quantity' => 3, 'origin_warehouse_id' => $data['warehouse_id'],
            'dest_warehouse_id' => $destination])->assertRedirect(route('internal.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory', ['product_id' => $productId, 'warehouse_id' => $data['warehouse_id'], 'theoretical_balance' => 9]);
        $this->assertDatabaseHas('inventory', ['product_id' => $productId, 'warehouse_id' => $destination, 'theoretical_balance' => 3]);
    }

    public function test_movement_balances_require_authentication_and_valid_selections(): void
    {
        $this->getJson(route('movements.balances'))->assertUnauthorized();
        $this->actingAs(User::factory()->create());
        $this->getJson(route('movements.balances', [
            'product_id' => 99999, 'product_condition' => 'Inexistente', 'warehouse_ids' => [99999],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['product_id', 'product_condition', 'warehouse_ids.0']);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_movement_balances_follow_product_condition_warehouse_and_current_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $productId = $data['items'][0]['product_id'];
        $origin = $data['warehouse_id'];
        $destination = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Destino para consultar']);
        DB::table('inventory')->insert([
            ['product_id' => $productId, 'warehouse_id' => $origin, 'product_condition' => 'Reforma', 'theoretical_balance' => 4],
            ['product_id' => $productId, 'warehouse_id' => $destination, 'product_condition' => 'Novo', 'theoretical_balance' => 3],
        ]);
        $query = ['product_id' => $productId, 'product_condition' => 'Novo', 'warehouse_ids' => [$origin, $destination]];
        $response = $this->getJson(route('movements.balances', $query))->assertOk()
            ->assertJsonPath('balances.0.balance', 10)->assertJsonPath('balances.1.balance', 3);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->getJson(route('movements.balances', array_replace($query, ['product_condition' => 'Reforma'])))
            ->assertOk()->assertJsonPath('balances.0.balance', 4)->assertJsonPath('balances.1.balance', 0);
        $this->getJson(route('movements.balances', array_replace($query, ['product_id' => $data['items'][1]['product_id']])))
            ->assertOk()->assertJsonPath('balances.0.balance', 20)->assertJsonPath('balances.1.balance', 0);
        $this->assertDatabaseCount('inventory', 4);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->post('/entradas', ['date' => '2026-11-13T10:00', 'product_id' => $productId,
            'product_condition' => 'Novo', 'quantity' => 2, 'dest_warehouse_id' => $origin,
            'is_fiscal' => false])->assertRedirect(route('entrance.index'))->assertSessionHasNoErrors();
        $this->getJson(route('movements.balances', $query))->assertOk()
            ->assertJsonPath('balances.0.balance', 12)->assertJsonPath('balances.1.balance', 3);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_movement_forms_render_readonly_balances_for_their_selected_sides(): void
    {
        $this->actingAs(User::factory()->create());
        $this->catalog();
        $this->get('/entradas')->assertOk()
            ->assertSee('data-movement-balance="destination" readonly', false)
            ->assertDontSee('data-movement-balance="origin"', false);
        $this->get('/internas')->assertOk()
            ->assertSee('data-movement-balance="origin" readonly', false)
            ->assertSee('data-movement-balance="destination" readonly', false)
            ->assertSee('Atualizar saldos');
    }

    public function test_invoice_update_and_delete_require_authentication(): void
    {
        $data = $this->catalog();
        $this->actingAs(User::factory()->create());
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->app['auth']->forgetGuards();
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => 'Paga'])->assertUnauthorized();
        $this->deleteJson('/api/invoices/'.$invoice->invoice_id)->assertUnauthorized();
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'payment_status' => 'A vencer']);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_imported_header_rejects_every_immutable_field_even_when_empty(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $original = $invoice->getAttributes();
        foreach (['issuer_document', 'customer_id', 'invoice_id', 'invoice_number', 'invoice_serie', 'issue_date',
            'due_date', 'payment_method', 'delivery_driver', 'payment_terms_days', 'payment_date', 'amount',
            'access_key', 'stock_posted_at', 'created_at', 'updated_at', 'items'] as $field) {
            $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => 'Paga', $field => null])
                ->assertUnprocessable()->assertJsonValidationErrors($field)
                ->assertJsonPath('errors.'.$field.'.0', 'Os dados da nota importada são somente para leitura. Altere apenas a situação do pagamento.');
        }
        foreach (['Vencida', 'Cancelada'] as $status) {
            $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => $status])
                ->assertUnprocessable()->assertJsonValidationErrors('payment_status');
        }
        $this->assertSame($original, $invoice->fresh()->getAttributes());
        $this->assertDatabaseCount('invoice_items', 2);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 8]);
    }

    public function test_access_key_alone_prevents_deleting_imported_invoice_or_adding_items(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $invoice = Invoice::query()->create(['invoice_number' => 123, 'invoice_serie' => 1,
            'customer_id' => $data['customer_id'], 'issue_date' => '2026-11-13', 'due_date' => '2026-12-13',
            'payment_method' => 'Boleto', 'payment_terms_days' => 30, 'payment_status' => 'A vencer',
            'amount' => 0, 'access_key' => str_repeat('1', 44)]);
        $this->deleteJson('/api/invoices/'.$invoice->invoice_id)->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
        $this->postJson('/api/invoice-items', ['invoice_id' => $invoice->invoice_id,
            'product_id' => $data['items'][0]['product_id'], 'quantity' => 1])->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('invoice_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_due_today_is_not_overdue_until_the_next_day_in_sao_paulo_and_payment_only_changes_finances(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->travelTo(Carbon::parse('2026-12-13 23:59:00', 'America/Sao_Paulo'));
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->get('/')->assertOk()->assertViewHas('receivables', fn (array $rows): bool => $rows[0]['status'] === 'A vencer');
        $this->getJson('/api/reports/upcoming-invoices?days=0')->assertOk()->assertJsonCount(1, 'data');
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'payment_status' => 'A vencer']);
        $this->travelTo(Carbon::parse('2026-12-14 00:01:00', 'America/Sao_Paulo'));
        $this->getJson('/api/invoices/'.$invoice->invoice_id)->assertOk()->assertJsonPath('data.payment_status', 'Vencida');
        $this->get('/')->assertOk()->assertViewHas('receivables', fn (array $rows): bool => $rows[0]['status'] === 'Vencida');
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => 'Paga'])->assertOk()
            ->assertJsonPath('data.payment_status', 'Paga')->assertJsonPath('data.payment_date', '2026-12-14');
        $this->travelTo(Carbon::parse('2026-12-15 10:00:00', 'America/Sao_Paulo'));
        $this->getJson('/api/invoices')->assertOk()->assertJsonPath('data.0.payment_status', 'Paga');
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => 'Paga'])->assertOk()
            ->assertJsonPath('data.payment_date', '2026-12-14');
        $this->patchJson('/api/invoices/'.$invoice->invoice_id, ['payment_status' => 'A vencer'])->assertOk()
            ->assertJsonPath('data.payment_status', 'Vencida')->assertJsonPath('data.payment_date', null);
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'amount' => 190]);
        $this->assertSame('2026-12-13', $invoice->fresh()->due_date->toDateString());
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][0]['product_id'], 'theoretical_balance' => 8]);
        $this->assertDatabaseHas('inventory', ['product_id' => $data['items'][1]['product_id'], 'theoretical_balance' => 17]);
    }

    public function test_importing_an_already_overdue_unpaid_invoice_sets_status_automatically(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->travelTo(Carbon::parse('2026-12-14 10:00:00', 'America/Sao_Paulo'));
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $this->assertSame('Vencida', $invoice->payment_status);
        $this->assertNull($invoice->payment_date);
        $this->assertSame('2026-12-13', $invoice->due_date->toDateString());
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_overdue_command_preserves_paid_and_cancelled_invoices_and_is_idempotent(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $paid = $invoice->replicate()->fill(['invoice_number' => 900010, 'access_key' => str_repeat('2', 44), 'payment_status' => 'Paga', 'payment_date' => '2026-11-13']);
        $paid->save();
        $cancelled = $invoice->replicate()->fill(['invoice_number' => 900011, 'access_key' => str_repeat('3', 44), 'payment_status' => 'Cancelada']);
        $cancelled->save();
        $future = $invoice->replicate()->fill(['invoice_number' => 900012, 'access_key' => str_repeat('4', 44), 'due_date' => '2027-01-20']);
        $future->save();
        Invoice::query()->whereKey($future->invoice_id)->update(['payment_status' => 'Vencida']);
        $this->travelTo(Carbon::parse('2026-12-14 10:00:00', 'America/Sao_Paulo'));
        $this->artisan('invoices:refresh-payment-status')->expectsOutput('Notas com situação de vencimento atualizada: 2')->assertSuccessful();
        $this->artisan('invoices:refresh-payment-status')->expectsOutput('Notas com situação de vencimento atualizada: 0')->assertSuccessful();
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'payment_status' => 'Vencida']);
        $this->assertDatabaseHas('invoices', ['invoice_id' => $paid->invoice_id, 'payment_status' => 'Paga']);
        $this->assertSame('2026-11-13', $paid->fresh()->payment_date->toDateString());
        $this->assertDatabaseHas('invoices', ['invoice_id' => $cancelled->invoice_id, 'payment_status' => 'Cancelada']);
        $this->assertDatabaseHas('invoices', ['invoice_id' => $future->invoice_id, 'payment_status' => 'A vencer']);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_imported_invoice_form_exposes_only_payment_status(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $this->postJson('/api/invoices/import', $data + ['xml_file' => $this->upload()])->assertCreated();
        $invoice = Invoice::query()->sole();
        $response = $this->get('/consulta-nfe')->assertOk()->assertSee('Alterar situação')->assertSee('Salvar situação');
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $prefix = '//*[@data-dialog-layer="invoice-edit-'.$invoice->invoice_id.'"]';
        $this->assertSame(1, $xpath->query($prefix.'//select[@name="payment_status"]')->length);
        $this->assertSame(0, $xpath->query($prefix.'//input[not(@type="hidden")] | '.$prefix.'//textarea')->length);
        $this->assertSame(2, $xpath->query($prefix.'//select[@name="payment_status"]/option')->length);
        $this->assertSame('Não paga', $xpath->query($prefix.'//option[@value="A vencer"]')->item(0)->textContent);
    }

    public function test_customer_delete_action_follows_invoice_links_and_sales_is_not_in_the_sidebar(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->catalog();
        $linkedCustomer = Customer::query()->findOrFail($data['customer_id']);
        $freeCustomer = Customer::query()->create(['customer_name' => 'Cliente sem vínculos',
            'customer_document' => '22222222000191', 'customer_payment_terms' => 'À vista']);
        $countDeleteButtons = function (string $html, int $customerId): int {
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new \DOMXPath($dom);

            return $xpath->query('//tr[@data-customer-row][td[1][normalize-space(.)="'.$customerId.'"]]//button[@data-customer-delete]')->length;
        };
        $initialPage = $this->get('/clientes')->assertOk();
        $this->assertSame(1, $countDeleteButtons($initialPage->getContent(), $linkedCustomer->customer_id));
        $this->assertSame(1, $countDeleteButtons($initialPage->getContent(), $freeCustomer->customer_id));
        $this->postJson('/api/invoices', ['invoice_number' => 852, 'invoice_serie' => 1,
            'customer_id' => $linkedCustomer->customer_id, 'issue_date' => '2026-11-13', 'due_date' => '2026-12-13',
            'payment_method' => 'Boleto', 'payment_terms_days' => 30])->assertCreated();
        $invoice = Invoice::query()->sole();
        $linkedPage = $this->get('/clientes')->assertOk()
            ->assertSee('aria-label="Ver detalhes de '.$linkedCustomer->customer_name.'"', false)
            ->assertSee('aria-label="Editar cliente '.$linkedCustomer->customer_name.'"', false)
            ->assertDontSee('href="'.route('sales.index').'"', false)
            ->assertSee('href="'.route('invoice-consult.index').'"', false);
        $this->assertSame(0, $countDeleteButtons($linkedPage->getContent(), $linkedCustomer->customer_id));
        $this->assertSame(1, $countDeleteButtons($linkedPage->getContent(), $freeCustomer->customer_id));
        $this->deleteJson('/api/customers/'.$linkedCustomer->customer_id)->assertConflict()
            ->assertJsonPath('message', 'Clientes vinculados a notas fiscais não podem ser excluídos.');
        $this->delete(route('customers.destroy', $linkedCustomer))->assertRedirect(route('customers.index'))
            ->assertSessionHas('error', 'Clientes vinculados a notas fiscais não podem ser excluídos.');
        $this->assertDatabaseHas('customers', ['customer_id' => $linkedCustomer->customer_id]);
        $this->assertDatabaseHas('invoices', ['invoice_id' => $invoice->invoice_id, 'customer_id' => $linkedCustomer->customer_id]);
        $this->delete(route('customers.destroy', $freeCustomer))->assertRedirect(route('customers.index'))
            ->assertSessionHas('status', 'Cliente excluído com sucesso.');
        $this->assertDatabaseMissing('customers', ['customer_id' => $freeCustomer->customer_id]);
        $this->deleteJson('/api/invoices/'.$invoice->invoice_id)->assertOk();
        $unlinkedPage = $this->get('/clientes')->assertOk();
        $this->assertSame(1, $countDeleteButtons($unlinkedPage->getContent(), $linkedCustomer->customer_id));
        $this->deleteJson('/api/customers/'.$linkedCustomer->customer_id)->assertOk();
        $this->assertDatabaseMissing('customers', ['customer_id' => $linkedCustomer->customer_id]);
    }

    private function catalog(): array
    {
        $customer = Customer::query()->create(['customer_name' => 'Cliente fictício', 'customer_document' => '11.111.111/0001-91', 'customer_payment_terms' => '30 dias']);
        $warehouse = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Armazém teste']);
        $items = [];
        foreach (['A', 'B'] as $index => $suffix) {
            $product = Product::query()->create(['sku' => 'INTERNO-'.$suffix, 'product_name' => 'Produto interno '.$suffix,
                'category' => 'PBR', 'material' => 'Madeira', 'cost_price' => $index === 0 ? 5 : 8, 'length_mm' => 1200, 'width_mm' => 1000]);
            DB::table('inventory')->insert(['warehouse_id' => $warehouse, 'product_id' => $product->product_id,
                'product_condition' => 'Novo', 'theoretical_balance' => $index === 0 ? 10 : 20]);
            $items[] = ['line_number' => $index + 1, 'product_id' => $product->product_id];
        }

        return ['customer_id' => $customer->customer_id, 'warehouse_id' => $warehouse, 'product_condition' => 'Novo',
            'due_date' => '2026-12-13', 'payment_method' => 'Boleto', 'payment_terms_days' => 30, 'payment_status' => 'A vencer', 'items' => $items];
    }

    private function upload(?string $contents = null): UploadedFile
    {
        $file = tempnam(sys_get_temp_dir(), 'jemp-xml-');
        file_put_contents($file, $contents ?? file_get_contents(base_path('tests/Fixtures/nfe-venda-ficticia.xml')));
        $this->temporaryFiles[] = $file;

        return new UploadedFile($file, 'nfe-ficticia.xml', 'application/xml', UPLOAD_ERR_OK, true);
    }

    private function changedDocument(int $number, string $issuer): string
    {
        $contents = file_get_contents(base_path('tests/Fixtures/nfe-venda-ficticia.xml'));
        $base = '352611'.$issuer.'55099'.str_pad((string) $number, 9, '0', STR_PAD_LEFT).'112345678';
        $sum = 0;
        for ($position = 42, $weight = 2; $position >= 0; $position--, $weight = $weight === 9 ? 2 : $weight + 1) {
            $sum += (int) $base[$position] * $weight;
        }
        $digit = 11 - ($sum % 11);
        $key = $base.($digit >= 10 ? 0 : $digit);
        $contents = preg_replace('/Id="NFe\d{44}"/', 'Id="NFe'.$key.'"', $contents);

        return str_replace(['<nNF>900001</nNF>', '<CNPJ>99999999000191</CNPJ>'], ['<nNF>'.$number.'</nNF>', '<CNPJ>'.$issuer.'</CNPJ>'], $contents);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table): void {
            $table->id('customer_id');
            $table->string('customer_name');
            $table->string('customer_document')->unique();
            $table->string('customer_contact')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_payment_terms');
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id('product_id');
            $table->string('sku')->unique();
            $table->string('product_name');
            $table->string('category');
            $table->string('material');
            $table->decimal('cost_price', 10, 2);
            $table->integer('length_mm');
            $table->integer('width_mm');
            $table->timestamps();
        });
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id('warehouse_id');
            $table->string('warehouse_name');
            $table->text('warehouse_description')->nullable();
            $table->timestamps();
        });
        Schema::create('inventory', function (Blueprint $table): void {
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition');
            $table->integer('theoretical_balance');
            $table->timestamps();
            $table->primary(['warehouse_id', 'product_id', 'product_condition']);
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id('invoice_id');
            $table->integer('invoice_number');
            $table->integer('invoice_serie');
            $table->unsignedBigInteger('customer_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('payment_method');
            $table->string('delivery_driver')->nullable();
            $table->integer('payment_terms_days');
            $table->string('payment_status')->default('A vencer');
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['invoice_number', 'invoice_serie'], 'uq_invoices_number_serie');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id('item_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id('sm_id');
            $table->unsignedBigInteger('origin_warehouse_id')->nullable();
            $table->unsignedBigInteger('dest_warehouse_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('product_condition');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->integer('quantity');
            $table->string('movement_type');
            $table->string('movement_justification')->nullable();
            $table->dateTime('movement_date');
            $table->boolean('is_fiscal');
            $table->timestamps();
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->foreign(['origin_warehouse_id', 'product_id', 'product_condition'])->references(['warehouse_id', 'product_id', 'product_condition'])->on('inventory');
        });
        (require database_path('migrations/2026_11_13_191800_add_xml_sales_import_support.php'))->up();
    }
}
