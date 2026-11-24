<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WarehouseRequestWorkflowTest extends TestCase
{
    use SetsUpInvoicePayments;

    private const REQUEST = [
        'warehouse_name' => 'Setor Norte',
        'warehouse_description' => 'Estocagem de paletes novos',
        'warehouse_request_reason' => 'Atender a nova linha de produção',
    ];

    public function test_guests_cannot_request_or_inspect_warehouses(): void
    {
        $this->get('/armazens')->assertRedirectToRoute('login');
        $this->postJson('/api/warehouses', self::REQUEST)->assertUnauthorized();
        $this->getJson('/api/warehouses')->assertUnauthorized();
        $this->assertDatabaseCount('warehouses', 0);
    }

    public function test_request_is_attributed_to_the_signed_in_user_and_opens_the_requested_list(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/armazens', self::REQUEST);

        $response->assertRedirectToRoute('warehouses.index', ['situacao' => 'Solicitado']);
        $this->assertDatabaseHas('warehouses', self::REQUEST + [
            'warehouse_situation' => 'Solicitado', 'requested_by' => $user->id, 'warehouse_cancelation_reason' => null,
        ]);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Setor Norte')
            ->assertSee($user->name)->assertSee(self::REQUEST['warehouse_request_reason']);
        $this->get('/armazens')->assertOk()->assertDontSee('Setor Norte')->assertSee('warehouse-request-status')
            ->assertSeeInOrder(['title="Produtos"', 'title="Clientes"', 'title="Armazéns"'], false);
    }

    public function test_users_cannot_decide_status_impersonate_requesters_edit_or_delete(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/warehouses', self::REQUEST + [
            'warehouse_situation' => 'Ativo', 'requested_by' => $user->id + 1, 'warehouse_cancelation_reason' => 'Recusado', 'warehouse_id' => 7,
        ])->assertUnprocessable()->assertJsonValidationErrors(['warehouse_situation', 'requested_by', 'warehouse_cancelation_reason', 'warehouse_id']);
        $this->postJson('/api/warehouses', ['warehouse_name' => 'Novo'])
            ->assertUnprocessable()->assertJsonValidationErrors(['warehouse_description', 'warehouse_request_reason']);
        $id = $this->postJson('/api/warehouses', self::REQUEST)->assertCreated()
            ->assertJsonPath('data.warehouse_situation', 'Solicitado')->json('data.warehouse_id');
        $this->postJson('/api/warehouses', self::REQUEST)->assertUnprocessable()->assertJsonValidationErrors('warehouse_name');
        $this->patchJson("/api/warehouses/$id", ['warehouse_situation' => 'Ativo'])->assertMethodNotAllowed();
        $this->deleteJson("/api/warehouses/$id")->assertMethodNotAllowed();
        $this->assertDatabaseHas('warehouses', ['warehouse_id' => $id, 'warehouse_situation' => 'Solicitado']);
    }

    public function test_listing_filters_by_status_name_and_id_and_shows_the_refusal_reason(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $refused = DB::table('warehouses')->insertGetId(self::REQUEST + [
            'warehouse_situation' => 'Recusado', 'requested_by' => $user->id, 'warehouse_cancelation_reason' => 'Espaço já atendido',
        ]);
        DB::table('warehouses')->insert(['warehouse_name' => 'Galpão Sul', 'warehouse_situation' => 'Inativo']);
        DB::table('warehouses')->insert(['warehouse_name' => 'Galpão Central', 'warehouse_situation' => 'Ativo']);

        $this->get('/armazens')->assertOk()->assertSee('Galpão Central')->assertDontSee('Galpão Sul')->assertDontSee('Setor Norte');
        $this->get('/armazens?situacao=Recusado&busca='.$refused)->assertOk()->assertSee('Setor Norte')
            ->assertSee('Espaço já atendido')->assertDontSee('Galpão Central');
        $this->get('/armazens?situacao=Todas&busca=Galp')->assertOk()->assertSee('Galpão Central')->assertSee('Galpão Sul')->assertDontSee('Setor Norte');
        $this->get('/armazens?situacao=Inativo')->assertOk()->assertSee('Galpão Sul')->assertDontSee('Galpão Central');
    }

    public function test_legacy_stock_keeps_its_warehouse_id_and_new_rows_default_to_requested(): void
    {
        $catalog = $this->catalog();
        $migration = require database_path('alterations/2026_11_24_015651_add_request_workflow_to_warehouses.php');
        $migration->down();
        $migration->up();

        $this->assertDatabaseHas('warehouses', ['warehouse_id' => $catalog['warehouse_id'], 'warehouse_situation' => 'Ativo', 'requested_by' => null]);
        $this->assertDatabaseHas('inventory', ['warehouse_id' => $catalog['warehouse_id'], 'theoretical_balance' => 10]);
        $id = DB::table('warehouses')->insertGetId(self::REQUEST + ['requested_by' => auth()->id()]);
        $this->assertDatabaseHas('warehouses', ['warehouse_id' => $id, 'warehouse_situation' => 'Solicitado']);

        $this->expectException(QueryException::class);
        DB::table('warehouses')->where('warehouse_id', $id)->update(['warehouse_situation' => 'Recusado', 'warehouse_cancelation_reason' => '  ']);
    }

    public static function unavailableSituations(): array
    {
        return [['Inativo'], ['Recusado'], ['Solicitado']];
    }

    #[DataProvider('unavailableSituations')]
    public function test_unavailable_warehouses_cannot_be_used_in_stock_operations(string $situation): void
    {
        $catalog = $this->catalog();
        $id = $catalog['warehouse_id'];
        DB::table('warehouses')->where('warehouse_id', $id)->update([
            'warehouse_situation' => $situation, 'warehouse_request_reason' => 'Avaliação',
            'warehouse_cancelation_reason' => $situation === 'Recusado' ? 'Espaço indisponível' : null, 'requested_by' => auth()->id(),
        ]);
        $other = DB::table('warehouses')->insertGetId(['warehouse_name' => 'Unidade ativa', 'warehouse_situation' => 'Ativo']);
        $product = DB::table('products')->min('product_id');
        $movement = ['product_id' => $product, 'product_condition' => 'Novo', 'quantity' => 1, 'date' => '2026-11-19'];
        $stock = ['product_id' => $product, 'product_condition' => 'Novo', 'theoretical_balance' => 5];

        $this->get(route('entrance.index'))->assertOk()->assertViewHas('locations', fn (array $locations) => array_keys($locations) === [$other]);
        $this->get(route('invoice-consult.index'))->assertOk()->assertViewHas('warehouses', fn ($locations) => $locations->modelKeys() === [$other]);
        $this->post('/entradas', $movement + ['dest_warehouse_id' => $id])->assertSessionHasErrors('dest_warehouse_id');
        $this->post('/internas', $movement + ['origin_warehouse_id' => $id, 'dest_warehouse_id' => $other])->assertSessionHasErrors('origin_warehouse_id');
        $this->post('/internas', $movement + ['origin_warehouse_id' => $other, 'dest_warehouse_id' => $id])->assertSessionHasErrors('dest_warehouse_id');
        $this->getJson(route('movements.balances', ['product_id' => $product, 'product_condition' => 'Novo', 'warehouse_ids' => [$id]]))
            ->assertUnprocessable()->assertJsonValidationErrors('warehouse_ids.0');
        $this->postJson('/api/invoices/import', $catalog + ['xml_file' => $this->upload()])->assertUnprocessable()->assertJsonValidationErrors('warehouse_id');
        $this->post('/produtos', ['warehouse_id' => $id])->assertSessionHasErrors('warehouse_id');
        $this->postJson('/api/inventories', $stock + ['warehouse_id' => $id])->assertUnprocessable()->assertJsonValidationErrors('warehouse_id');
        $this->patchJson("/api/inventories/$id~$product~Novo", ['theoretical_balance' => 2])->assertUnprocessable();
        $this->deleteJson("/api/inventories/$id~$product~Novo")->assertUnprocessable();
        $this->deleteJson('/api/products/'.$product)->assertUnprocessable()->assertJsonValidationErrors('warehouse_id');
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseHas('inventory', ['warehouse_id' => $id, 'product_id' => $product, 'theoretical_balance' => 10]);
    }
}
