<x-layouts.app title="Produtos · JEMP" page="products">
    @php
        $columns = [
            ['key' => 'code', 'label' => 'Cód.'], ['key' => 'name', 'label' => 'Nome do Produto'],
            ['key' => 'category', 'label' => 'Categoria'], ['key' => 'type', 'label' => 'Condição'],
            ['key' => 'measure', 'label' => 'Medida'], ['key' => 'location', 'label' => 'Local'],
            ['key' => 'material', 'label' => 'Material'], ['key' => 'cost_price_display', 'label' => 'Custo'],
            ['key' => 'actions', 'label' => 'Ações', 'type' => 'actions', 'idKey' => 'id', 'actions' => [
                ['name' => 'view', 'label' => 'Ver detalhes', 'icon' => 'eye'],
                ['name' => 'edit', 'label' => 'Editar produto', 'icon' => 'pencil-simple'],
                ['name' => 'delete', 'label' => 'Excluir produto', 'icon' => 'trash'],
            ]],
        ];
        $rows = $products->map(fn ($product) => [
            'id' => $product->product_id,
            'product_id' => $product->product_id,
            'code' => $product->sku,
            'name' => $product->product_name,
            'sku' => $product->sku,
            'product_name' => $product->product_name,
            'category' => $product->category,
            'type' => $product->inventory->pluck('product_condition')->unique()->implode(', ') ?: '—',
            'measure' => $product->width_mm.' × '.$product->length_mm.' mm',
            'location' => $product->inventory->first()?->warehouse?->warehouse_name ?? '—',
            'material' => $product->material,
            'cost_price' => $product->cost_price,
            'cost_price_display' => 'R$ '.number_format((float) $product->cost_price, 2, ',', '.'),
            'width_mm' => $product->width_mm,
            'length_mm' => $product->length_mm,
            'theoretical_balance' => $product->inventory_sum_theoretical_balance ?? 0,
            'warehouse_id' => $product->inventory->first()?->warehouse_id,
            'product_condition' => $product->inventory->first()?->product_condition ?? 'Novo',
            'updated_at' => $product->updated_at,
            'inventory' => $product->inventory->map(fn ($stock) => [
                'warehouse_id' => $stock->warehouse_id,
                'warehouse' => $stock->warehouse?->warehouse_name,
                'product_condition' => $stock->product_condition,
                'theoretical_balance' => $stock->theoretical_balance,
            ])->values(),
            '_data' => [
                'search' => implode(' ', [$product->sku, $product->product_name, $product->category, $product->material]),
                'update-url' => route('products.update', $product),
                'delete-url' => route('products.destroy', $product),
            ],
        ])->all();
        $fields = [
            ['name' => 'sku', 'id' => 'product-sku', 'label' => 'SKU gerado pelo sistema', 'readonly' => true, 'value' => 'Gerado ao cadastrar'],
            ['name' => 'product_name', 'id' => 'product-name', 'label' => 'Produto', 'required' => true],
            ['name' => 'category', 'id' => 'product-category', 'label' => 'Categoria', 'type' => 'select', 'options' => ['PBR' => 'PBR', 'Descartável' => 'Descartável', 'Não Standard' => 'Não Standard'], 'required' => true],
            ['name' => 'material', 'id' => 'product-material', 'label' => 'Material', 'required' => true],
            ['name' => 'cost_price', 'id' => 'product-cost-price', 'label' => 'Custo unitário', 'type' => 'number', 'prefix' => 'R$', 'min' => 0, 'step' => 0.01, 'required' => true],
            ['name' => 'width_mm', 'id' => 'product-width', 'label' => 'Largura (mm)', 'type' => 'number', 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'length_mm', 'id' => 'product-length', 'label' => 'Comprimento (mm)', 'type' => 'number', 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'warehouse_id', 'id' => 'product-warehouse', 'label' => 'Armazém inicial', 'type' => 'select', 'options' => $warehouses->mapWithKeys(fn ($warehouse) => [$warehouse->warehouse_id => $warehouse->warehouse_name])->all(), 'required' => true, 'createOnly' => true],
            ['name' => 'product_condition', 'id' => 'product-condition', 'label' => 'Condição do estoque', 'type' => 'select', 'options' => ['Novo' => 'Novo', 'Reforma' => 'Reforma', 'Descarte' => 'Descarte'], 'required' => true, 'createOnly' => true],
            ['name' => 'theoretical_balance', 'id' => 'product-balance', 'label' => 'Saldo inicial', 'type' => 'number', 'min' => 0, 'step' => 1, 'required' => true, 'createOnly' => true],
        ];
        $inventoryColumns = [
            ['key' => 'warehouse', 'label' => 'Armazém'],
            ['key' => 'product_condition', 'label' => 'Condição'],
            ['key' => 'theoretical_balance', 'label' => 'Saldo teórico'],
            ['key' => 'status', 'label' => 'Situação', 'type' => 'status'],
        ];
    @endphp

    <div class="products-page" data-product-catalog data-validation-errors="{{ $errors->any() ? 'true' : 'false' }}" data-old-record-id="{{ old('_record_id', '') }}">
        <div class="page-head"><h1>Produtos</h1></div>
        @if ($errors->any()) <p class="products-error" role="alert">{{ $errors->first() }}</p> @endif
        @if (session('error')) <p class="products-error" role="alert">{{ session('error') }}</p> @endif
        @if (session('error')) <p class="products-error" role="alert">{{ session('error') }}</p> @endif
        <section class="products-panel" aria-labelledby="products-list-title">
            <header class="products-panel__header">
                <div><h2 id="products-list-title">Listagem de Produtos</h2><p>Gerencie todos os produtos do seu catálogo.</p></div>
                <x-buttons.button class="products-create" data-product-create><x-icons.icon name="plus" size="sm" /> Novo Produto</x-buttons.button>
            </header>
            <div class="products-panel__body">
                <x-filters.filter-bar class="products-filters" label="Filtrar produtos" data-product-filters>
                    <label class="products-search"><x-icons.icon name="magnifying-glass" size="sm" /><span class="sr-only">Buscar produto</span><input type="search" placeholder="Buscar produto..." data-product-search></label>
                    <label>Categoria<select data-product-category><option value="">Todas as categorias</option><option>PBR</option><option>Descartável</option><option>Não Standard</option></select></label>
                    <label>Condição<select data-product-type><option value="">Todas as condições</option>@foreach ($products->flatMap(fn ($product) => $product->inventory->pluck('product_condition'))->unique()->sort() as $condition)<option>{{ $condition }}</option>@endforeach</select></label>
                </x-filters.filter-bar>
                <x-tables.table :columns="$columns" :rows="$rows" variant="spreadsheet" caption="Listagem de produtos" />
                <footer class="products-footer">
                    <p class="products-summary" data-product-count aria-live="polite">{{ count($products) }} {{ count($products) === 1 ? 'produto' : 'produtos' }}</p>
                    <nav class="products-pagination" aria-label="Páginas do catálogo"><button type="button" data-product-prev disabled>Anterior</button><span data-product-pages></span><button type="button" data-product-next disabled>Próximo</button></nav>
                </footer>
            </div>
        </section>

        <x-dialogs.modal label="Novo produto" class="products-modal products-modal--form" data-product-form-modal data-product-store-url="{{ route('products.store') }}">
            <x-dialogs.form-dialog title="Novo produto" description="Informe os dados do produto para o catálogo." submit-label="Cadastrar produto" cancel-label="Cancelar" layout="grid" :fields="$fields" :action="route('products.store')" />
        </x-dialogs.modal>
        <x-dialogs.modal label="Detalhes do produto" class="products-modal products-modal--details" data-product-details-modal>
            <x-dialogs.details-dialog title="Detalhes do produto" :details="collect(['SKU', 'Produto', 'Categoria', 'Material', 'Custo unitário', 'Largura (mm)', 'Comprimento (mm)', 'Saldo total'])->map(fn ($label) => ['label' => $label, 'value' => '—'])->all()">
                <section class="products-inventory" aria-labelledby="products-inventory-title">
                    <div class="products-inventory__header">
                        <h3 id="products-inventory-title">Inventário do produto</h3>
                        <button class="products-stock-add" type="button" data-product-stock-open>Adicionar estoque</button>
                    </div>
                    <form class="products-stock-form" action="{{ route('entrance.store') }}" method="POST" data-product-stock-form hidden>
                        @csrf
                        <input type="hidden" name="product_id" data-product-stock-id>
                        <p class="products-stock-form__product">Entrada para <strong data-product-stock-name></strong></p>
                        <label>Condição
                            <select name="product_condition" required>
                                <option value="Novo">Novo</option>
                                <option value="Reforma">Reforma</option>
                                <option value="Descarte">Descarte</option>
                            </select>
                        </label>
                        <label>Armazém de destino
                            <select name="dest_warehouse_id" required>
                                <option value="">Selecione um armazém</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Data e hora
                            <input name="date" type="datetime-local" value="{{ now()->format('Y-m-d\\TH:i') }}" required>
                        </label>
                        <label>Quantidade
                            <input name="quantity" type="number" min="1" step="1" required>
                        </label>
                        <label>Justificativa
                            <textarea name="movement_justification" maxlength="255" rows="2"></textarea>
                        </label>
                        <div class="products-stock-form__actions">
                            <button type="button" class="btn btn-secondary" data-product-stock-cancel>Cancelar</button>
                            <button type="submit" class="btn btn-primary">Registrar entrada</button>
                        </div>
                    </form>
                    <x-tables.table :columns="$inventoryColumns" caption="Inventário por barracão" />
                </section>
            </x-dialogs.details-dialog>
            <template data-product-stock-available><x-status.badge label="Em estoque" variant="success" /></template>
            <template data-product-stock-empty><x-status.badge label="Sem estoque" variant="danger" /></template>
        </x-dialogs.modal>
        <x-dialogs.modal label="Excluir produto?" class="products-modal products-modal--delete" data-product-delete-modal>
            <form method="POST" data-product-delete-form>
                @csrf
                @method('DELETE')
                <x-dialogs.confirmation title="Excluir produto?" message="" confirm-label="Excluir produto" />
            </form>
        </x-dialogs.modal>
        <div class="products-notice" @if (!session('status')) hidden @endif data-product-notice><x-dialogs.alert variant="success" :message="session('status', '')" /></div>
    </div>
</x-layouts.app>
