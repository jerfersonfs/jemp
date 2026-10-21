<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductsPageTest extends TestCase
{
    public function test_catalog_renders_product_data_and_shared_dialogs(): void
    {
        $this->get('/produtos')
            ->assertOk()
            ->assertSee('Listagem de Produtos')
            ->assertSee('jemp-filter-bar')
            ->assertSee('jemp-table-region--spreadsheet')
            ->assertSee('data-row-action="view"', false)
            ->assertSee('data-row-action="edit"', false)
            ->assertSee('data-row-action="delete"', false)
            ->assertSee('1,00 × 1,20 m')
            ->assertSee('data-product-form-modal', false)
            ->assertSee('data-product-details-modal', false)
            ->assertSee('Inventário do produto')
            ->assertSee('Localização')
            ->assertSee('Barracão 3')
            ->assertSee('data-product-stock-available', false)
            ->assertSee('data-product-stock-empty', false)
            ->assertSee('data-product-delete-modal', false)
            ->assertSee('jemp-form-dialog--grid')
            ->assertSee('name="category"', false)
            ->assertSee('name="material"', false)
            ->assertSee('name="wood"', false)
            ->assertSee('min="0.01"', false)
            ->assertSee('Cancelar');
    }
}
