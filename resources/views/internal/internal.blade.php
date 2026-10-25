<x-layouts.app title="Movimentações internas · JEMP" page="internal">
    @php
        $transferRows = $movements->map(fn ($movement) => [
            'date' => \Illuminate\Support\Carbon::parse($movement->movement_date)->format('d/m/Y H:i'),
            'type' => $movement->movement_type,
            'product' => trim($movement->product_sku.' — '.$movement->product_name),
            'quantity' => $movement->quantity,
            'origin' => $movement->origin_warehouse_name,
            'destination' => $movement->destination_warehouse_name,
            'condition' => $movement->product_condition,
            'justification' => $movement->movement_justification,
            'fiscal' => $movement->is_fiscal ? 'Sim' : 'Não',
            'invoice' => $movement->invoice_id ?? '—',
            'responsible' => $movement->user_id,
            '_data' => ['origin' => $movement->origin_warehouse_id, 'destination' => $movement->dest_warehouse_id],
        ])->all();
        $transferColumns = [
            ['key' => 'date', 'label' => 'Data'], ['key' => 'product', 'label' => 'Nome do Produto'],
            ['key' => 'quantity', 'label' => 'Qtd'], ['key' => 'origin', 'label' => 'Origem'], ['key' => 'destination', 'label' => 'Destino'],
            ['key' => 'condition', 'label' => 'Condição'],
        ];
        $productOptions = $products->mapWithKeys(fn ($product) => [$product->product_id => $product->sku.' — '.$product->product_name])->all();
        $transferFormFields = [
            ['name' => 'product_id', 'label' => 'Produto', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um produto'] + $productOptions],
            ['name' => 'product_condition', 'label' => 'Condição', 'type' => 'select', 'required' => true, 'options' => ['Novo' => 'Novo', 'Reforma' => 'Reforma', 'Descarte' => 'Descarte']],
            ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'origin_warehouse_id', 'label' => 'Origem', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione a origem'] + $locations],
            ['name' => 'dest_warehouse_id', 'label' => 'Destino', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione o destino'] + $locations],
            ['name' => 'movement_justification', 'label' => 'Justificação', 'type' => 'textarea'],
        ];
    @endphp

    <div class="page-head movement-page__head"><div><h1>Transferências</h1></div></div>
    @if (session('status')) <p class="movement-feedback" role="status">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p class="movement-feedback movement-feedback--error" role="alert">{{ $errors->first() }}</p> @endif
    <x-cards.panel class="movement-panel movement-panel--internal" variant="mint" title="Histórico de Movimentações" description="Gerencie todas as movimentações entre os armazéns.">
        <x-slot:actions><button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova transferência"><x-icons.icon name="plus" size="sm" color="white" /> Nova Transferência</button></x-slot:actions>
        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar transferências" data-movement-filter-form>
                <div class="movement-search"><x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto..." aria-label="Buscar movimentações" data-movement-search><x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading></x-inputs.input></div>
                <x-inputs.input name="origin" label="Origem" type="select" size="sm" :options="['' => 'Todas as origens'] + $locations" data-movement-filter="origin" />
                <x-inputs.input name="destination" label="Destino" type="select" size="sm" :options="['' => 'Todos os destinos'] + $locations" data-movement-filter="destination" />
            </x-filters.filter-bar>
        </x-slot:filters>
        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="movimentações">
            <x-tables.table variant="spreadsheet" caption="Histórico de transferências internas" :columns="$transferColumns" :rows="$transferRows" empty-message="Nenhuma movimentação encontrada." />
            <div class="movement-table-footer"><span class="movement-table-footer__summary" data-movement-summary></span><nav class="movement-pagination" aria-label="Paginação das transferências"><button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button><span class="movement-pagination__current" data-page-current aria-live="polite">1 / 1</span><button class="btn btn-secondary" type="button" data-page-next disabled>Próximo <x-icons.icon name="caret-right" size="sm" /></button></nav></div>
        </div>
        <x-slot:footer><p class="movement-demo-note">Dados carregados do banco de dados.</p></x-slot:footer>
    </x-cards.panel>
    <x-movements.record-dialogs entity="transferência" create-title="Nova transferência" description="Informe o produto, a quantidade e os armazéns de origem e destino." :fields="$transferFormFields" :action="route('internal.store')" />
</x-layouts.app>
