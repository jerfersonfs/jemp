<?php

namespace Tests\Feature;

use Tests\TestCase;

class EntrancePageTest extends TestCase
{
    public function test_entrance_page_renders_shared_components_and_dialog_fields(): void
    {
        $this->get('/entradas')
            ->assertOk()
            ->assertSee('Listagem de Entradas')
        ->assertSee('data-movement-action="view"', false)
        ->assertSee('data-movement-action="edit"', false)
        ->assertSee('data-movement-action="delete"', false)
            ->assertSee('type="datetime-local"', false)
            ->assertSee('min="1"', false)
            ->assertSee('jemp-form-dialog--grid')
            ->assertSee('data-movement-dialog="details"', false)
            ->assertSee('data-movement-dialog="confirm"', false)
            ->assertSee('Fornecedor Central');
    }

    public function test_existing_pages_remain_accessible_after_component_changes(): void
    {
        $this->get('/')->assertOk();
        $this->get('/produtos')->assertOk();
    }
}
