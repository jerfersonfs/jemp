<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');
        (require database_path('alterations/2026_11_22_164053_create_stock_condition_settings.php'))->up();

        (require database_path('migrations/2026_11_21_185033_create_company_settings_table.php'))->up();
    }

    public function test_guests_cannot_read_or_change_the_company_threshold(): void
    {
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
        $this->put(route('settings.update'), ['low_stock_threshold' => 5])->assertRedirect(route('login'));
        $this->assertDatabaseHas('company_settings', ['id' => 1, 'low_stock_threshold' => 20]);
    }

    public function test_threshold_and_conditions_reclassify_existing_stock_after_saving(): void
    {
        DB::statement("CREATE VIEW vw_estoque AS SELECT 'Novo' AS product_condition, 10 AS theoretical_balance UNION ALL SELECT 'Reforma', 11 UNION ALL SELECT 'Descarte', 0");
        (require database_path('alterations/2026_11_22_164231_apply_saleable_conditions_to_stock_view.php'))->up();
        $this->actingAs(User::factory()->make(['id' => 1]));
        $this->put(route('settings.update'), [
            'low_stock_threshold' => 10, 'edit_conditions' => 1, 'saleable_conditions' => ['Novo', 'Reforma'],
        ])->assertSessionHasNoErrors();
        $statuses = DB::table('vw_estoque_atual')->pluck('stock_status', 'product_condition');
        $this->assertSame('Estoque baixo', $statuses['Novo']);
        $this->assertSame('Normal', $statuses['Reforma']);
        $this->assertSame('Fora de venda', $statuses['Descarte']);

        $this->put(route('settings.update'), [
            'low_stock_threshold' => 0, 'edit_conditions' => 1, 'saleable_conditions' => ['Descarte', 'Novo'],
        ])->assertSessionHasNoErrors();
        $statuses = DB::table('vw_estoque_atual')->pluck('stock_status', 'product_condition');
        $this->assertSame('Normal', $statuses['Novo']);
        $this->assertSame('Zerado', $statuses['Descarte']);
        $this->put(route('settings.update'), ['low_stock_threshold' => 0, 'edit_conditions' => 1])->assertSessionHasNoErrors();
        $this->assertSame(0, DB::table('stock_condition_settings')->where('saleable', true)->count());
    }

    #[TestWith([0])]
    #[TestWith([10])]
    public function test_a_saved_limit_is_visible_to_another_user(int $limit): void
    {
        $this->actingAs(User::factory()->make(['id' => 1]));
        $this->get(route('settings.edit'))->assertOk()->assertViewHas('lowStockThreshold', 20);
        $this->put(route('settings.update'), ['low_stock_threshold' => $limit])
            ->assertRedirect(route('settings.edit'))->assertSessionHasNoErrors()->assertSessionHas('status');

        $this->actingAs(User::factory()->make(['id' => 2]));
        $this->get(route('settings.edit'))->assertOk()->assertViewHas('lowStockThreshold', $limit);
        $this->assertDatabaseCount('company_settings', 1);
    }

    #[TestWith([''])]
    #[TestWith(['-1'])]
    #[TestWith(['2.5'])]
    #[TestWith(['2147483648'])]
    public function test_an_invalid_quantity_preserves_the_saved_threshold(string $invalid): void
    {
        $this->actingAs(User::factory()->make(['id' => 1]))
            ->from(route('settings.edit'))->put(route('settings.update'), ['low_stock_threshold' => $invalid])
            ->assertRedirect(route('settings.edit'))->assertSessionHasErrors('low_stock_threshold');

        $this->assertDatabaseHas('company_settings', ['id' => 1, 'low_stock_threshold' => 20]);
    }
}
