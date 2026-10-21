<x-layouts.app title="Produtos · JEMP" page="products">
    @php
        $columns = [
            ['key' => 'code', 'label' => 'Cód.'], ['key' => 'name', 'label' => 'Nome do Produto'],
            ['key' => 'category', 'label' => 'Categoria'], ['key' => 'type', 'label' => 'Tipo'],
            ['key' => 'measure', 'label' => 'Medida'], ['key' => 'location', 'label' => 'Local'],
            ['key' => 'material', 'label' => 'Material'], ['key' => 'wood', 'label' => 'Madeira'],
            ['key' => 'actions', 'label' => 'Ações', 'type' => 'actions', 'idKey' => 'id', 'actions' => [
                ['name' => 'view', 'label' => 'Ver detalhes', 'icon' => 'eye'],
                ['name' => 'edit', 'label' => 'Editar produto', 'icon' => 'pencil-simple'],
                ['name' => 'delete', 'label' => 'Excluir produto', 'icon' => 'trash'],
            ]],
        ];
        $rows = $products->map(fn ($product) => [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category' => $product->category,
            'type' => $product->type,
            'width' => $product->width,
            'length' => $product->length,
            'quantity' => $product->quantity,
            'location' => $product->location,
            'material' => $product->material,
            'wood' => $product->wood,
            'measure' => number_format((float) $product->width, 2, ',', '.') . ' × ' . number_format((float) $product->length, 2, ',', '.') . ' m',
            '_data' => [
                'search' => implode(' ', [$product->code, $product->name, $product->type, $product->location]),
                'update-url' => route('products.update', $product),
                'delete-url' => route('products.destroy', $product),
            ],
        ])->all();
        $fields = [
            ['name' => 'code', 'id' => 'product-code', 'label' => 'Código', 'required' => true],
            ['name' => 'name', 'id' => 'product-name', 'label' => 'Produto', 'required' => true],
            ['name' => 'category', 'id' => 'product-category', 'label' => 'Categoria', 'type' => 'select', 'options' => ['Descartável' => 'Descartável', 'Retornável' => 'Retornável'], 'required' => true],
            ['name' => 'type', 'id' => 'product-type', 'label' => 'Tipo', 'type' => 'select', 'options' => ['PBR' => 'PBR', 'FP' => 'FP'], 'required' => true],
            ['name' => 'width', 'id' => 'product-width', 'label' => 'Largura (m)', 'type' => 'number', 'min' => 0.01, 'step' => 0.01, 'required' => true],
            ['name' => 'length', 'id' => 'product-length', 'label' => 'Comprimento (m)', 'type' => 'number', 'min' => 0.01, 'step' => 0.01, 'required' => true],
            ['name' => 'quantity', 'id' => 'product-quantity', 'label' => 'Saldo no local', 'type' => 'number', 'min' => 0, 'step' => 1, 'required' => true],
            ['name' => 'location', 'id' => 'product-location', 'label' => 'Local', 'type' => 'select', 'options' => ['Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2', 'Barracão 3' => 'Barracão 3'], 'required' => true],
            ['name' => 'material', 'id' => 'product-material', 'label' => 'Material', 'required' => true],
            ['name' => 'wood', 'id' => 'product-wood', 'label' => 'Madeira', 'required' => true],
        ];
        $inventoryColumns = [
            ['key' => 'code', 'label' => 'Cód.'],
            ['key' => 'item', 'label' => 'Item'],
            ['key' => 'balance', 'label' => 'Saldo'],
            ['key' => 'location', 'label' => 'Localização'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
        ];
    @endphp

    <div class="products-page" data-product-catalog data-validation-errors="{{ $errors->any() ? 'true' : 'false' }}" data-old-record-id="{{ old('_record_id', '') }}">
        <div class="page-head"><h1>Produtos</h1></div>
        <section class="products-panel" aria-labelledby="products-list-title">
            <header class="products-panel__header">
                <div><h2 id="products-list-title">Listagem de Produtos</h2><p>Gerencie todos os produtos do seu catálogo.</p></div>
                <x-buttons.button class="products-create" data-product-create><x-icons.icon name="plus" size="sm" /> Novo Produto</x-buttons.button>
            </header>
            <div class="products-panel__body">
                <x-filters.filter-bar class="products-filters" label="Filtrar produtos" data-product-filters>
                    <label class="products-search"><x-icons.icon name="magnifying-glass" size="sm" /><span class="sr-only">Buscar produto</span><input type="search" placeholder="Buscar produto..." data-product-search></label>
                    <label>Categoria<select data-product-category><option value="">Todas as categorias</option><option>Descartável</option><option>Retornável</option></select></label>
                    <label>Tipo<select data-product-type><option value="">Todos os tipos</option><option>PBR</option><option>FP</option></select></label>
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
            <x-dialogs.details-dialog title="Detalhes do produto" :details="collect(['Código', 'Produto', 'Categoria', 'Tipo', 'Largura', 'Comprimento', 'Saldo', 'Localização', 'Material', 'Madeira'])->map(fn ($label) => ['label' => $label, 'value' => '—'])->all()">
                <section class="products-inventory" aria-labelledby="products-inventory-title">
                    <h3 id="products-inventory-title">Inventário do produto</h3>
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
