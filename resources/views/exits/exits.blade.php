<x-layouts.app title="Saídas · JEMP" page="exits">
    @php
        $exitRows = $movements->map(fn ($movement) => [
            'id' => $movement->id,
            'code' => $movement->code,
            'date' => $movement->occurred_at->format('d/m/Y'),
            'type' => $movement->type,
            'product' => $movement->product->name,
            'quantity' => $movement->quantity,
            'warehouse' => $movement->origin,
            'recipient' => $movement->recipient,
            '_data' => ['type' => $movement->type, 'warehouse' => $movement->origin],
        ])->all();
        $exitColumns = [
            ['key' => 'code', 'label' => 'Código'], ['key' => 'date', 'label' => 'Data'], ['key' => 'type', 'label' => 'Tipo'],
            ['key' => 'product', 'label' => 'Produto'], ['key' => 'quantity', 'label' => 'Qtd. saída'], ['key' => 'warehouse', 'label' => 'Almoxarifado'],
            ['key' => 'recipient', 'label' => 'Destinatário'], ['key' => 'code', 'label' => 'Ações', 'type' => 'actions', 'actions' => ['view'], 'class' => 'movement-actions-column'],
        ];
        $productOptions = $products->mapWithKeys(fn ($product) => [$product->id => $product->code.' — '.$product->name])->all();
        $exitFormFields = [
            ['name' => 'date', 'label' => 'Data da saída', 'type' => 'date', 'required' => true],
            ['name' => 'type', 'label' => 'Tipo de saída', 'type' => 'select', 'required' => true, 'options' => ['Consumo interno' => 'Consumo interno', 'Avaria' => 'Avaria', 'Venda' => 'Venda', 'Ajuste de estoque' => 'Ajuste de estoque']],
            ['name' => 'product_id', 'label' => 'Produto', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um produto'] + $productOptions],
            ['name' => 'lot', 'label' => 'Lote'],
            ['name' => 'quantity', 'label' => 'Quantidade de saída', 'type' => 'number', 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'warehouse', 'label' => 'Almoxarifado', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um local'] + $locations],
            ['name' => 'recipient', 'label' => 'Destinatário'],
            ['name' => 'reference', 'label' => 'Documento de referência'],
            ['name' => 'responsible', 'label' => 'Responsável'],
            ['name' => 'notes', 'label' => 'Observações', 'type' => 'textarea'],
        ];
    @endphp

    <div class="page-head movement-page__head"><div><h1>Saídas</h1></div></div>
    @if (session('status')) <p class="movement-feedback" role="status">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p class="movement-feedback movement-feedback--error" role="alert">{{ $errors->first() }}</p> @endif
    <x-cards.panel class="exit-panel movement-panel movement-panel--exits" variant="mint" title="Histórico de Saídas" description="Acompanhe as saídas registradas e o saldo atualizado por local.">
        <x-slot:actions><button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova saída"><x-icons.icon name="plus" size="sm" color="white" /> Nova Saída</button></x-slot:actions>
        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar saídas" data-movement-filter-form>
                <div class="movement-search"><x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto ou documento..." aria-label="Buscar saídas" data-movement-search><x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading></x-inputs.input></div>
                <x-inputs.input name="type" label="Tipo" type="select" size="sm" :options="['' => 'Todos os tipos', 'Consumo interno' => 'Consumo interno', 'Avaria' => 'Avaria', 'Venda' => 'Venda', 'Ajuste de estoque' => 'Ajuste de estoque']" data-movement-filter="type" />
                <x-inputs.input name="warehouse" label="Almoxarifado" type="select" size="sm" :options="['' => 'Todos os locais'] + $locations" data-movement-filter="warehouse" />
            </x-filters.filter-bar>
        </x-slot:filters>
        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="saídas">
            <x-tables.table variant="spreadsheet" caption="Histórico de saídas de estoque" :columns="$exitColumns" :rows="$exitRows" empty-message="Nenhuma saída encontrada." />
            <div class="movement-table-footer"><span class="movement-table-footer__summary" data-movement-summary></span><nav class="movement-pagination" aria-label="Paginação das saídas"><button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button><span class="movement-pagination__current" data-page-current aria-live="polite">1 / 1</span><button class="btn btn-secondary" type="button" data-page-next disabled>Próximo <x-icons.icon name="caret-right" size="sm" /></button></nav></div>
        </div>
    </x-cards.panel>
    <x-movements.record-dialogs entity="saída" create-title="Nova saída" description="Informe os dados da saída e do produto retirado do estoque." :fields="$exitFormFields" :action="route('exits.store')" />
</x-layouts.app>
