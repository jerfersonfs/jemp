<x-layouts.app title="Entradas · JEMP" page="entrance">
    @php
        $entryRows = $movements->map(fn ($movement) => [
            'sm_id' => $movement->sm_id,
            'date' => \Illuminate\Support\Carbon::parse($movement->movement_date)->format('d/m/Y H:i'),
            'type' => $movement->movement_type,
            'product' => trim($movement->product_sku.' — '.$movement->product_name.' ('.$movement->product_condition.')'),
            'quantity' => $movement->quantity,
            'origin' => 'Externo',
            'destination' => $movement->destination_warehouse_name,
            'justification' => $movement->movement_justification,
            'fiscal' => $movement->is_fiscal ? 'Sim' : 'Não',
            'invoice' => $movement->invoice_id ?? '—',
            'responsible' => $movement->user_id,
            '_data' => ['type' => $movement->movement_type, 'origin' => 'Externo', 'destination' => $movement->dest_warehouse_id],
        ])->all();
        $entryColumns = [
            ['key' => 'sm_id', 'label' => 'ID'],
            ['key' => 'date', 'label' => 'Data'],
            ['key' => 'type', 'label' => 'Tipo'],
            ['key' => 'product', 'label' => 'Entrada'],
            ['key' => 'quantity', 'label' => 'Qtd'],
            ['key' => 'origin', 'label' => 'Origem'],
            ['key' => 'destination', 'label' => 'Destino'],
            ['key' => 'justification', 'label' => 'Justificação'],
        ];
        $productOptions = $products->mapWithKeys(fn ($product) => [$product->product_id => $product->sku.' — '.$product->product_name])->all();
        $entryFormFields = [
            ['name' => 'date', 'label' => 'Data e hora', 'type' => 'datetime-local', 'required' => true],
            ['name' => 'product_id', 'label' => 'Produto', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um produto'] + $productOptions],
            ['name' => 'product_condition', 'label' => 'Condição', 'type' => 'select', 'required' => true, 'options' => ['Novo' => 'Novo', 'Reforma' => 'Reforma', 'Descarte' => 'Descarte']],
            ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'dest_warehouse_id', 'label' => 'Destino', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um armazém'] + $locations],
            ['name' => 'movement_justification', 'label' => 'Justificação', 'type' => 'textarea'],
        ];
    @endphp

    <div class="page-head movement-page__head"><div><h1>Entradas</h1></div></div>
    @if (session('status')) <p class="movement-feedback" role="status">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p class="movement-feedback movement-feedback--error" role="alert">{{ $errors->first() }}</p> @endif

    <x-cards.panel class="movement-panel movement-panel--entries" variant="mint" title="Listagem de Entradas" description="Gerencie todas as entradas do seu catálogo.">
        <x-slot:actions>
            <button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova entrada">
                <x-icons.icon name="plus" size="sm" color="white" /> Nova Entrada
            </button>
        </x-slot:actions>
        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar entradas" data-movement-filter-form>
                <div class="movement-search"><x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto..." aria-label="Buscar entradas" data-movement-search><x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading></x-inputs.input></div>
                <x-inputs.input name="type" label="Tipo" type="select" size="sm" :options="['' => 'Todos os tipos', 'Entrada' => 'Entrada']" data-movement-filter="type" />
                <x-inputs.input name="destination" label="Destino" type="select" size="sm" :options="['' => 'Todos os armazéns'] + $locations" data-movement-filter="destination" />
            </x-filters.filter-bar>
        </x-slot:filters>
        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="entradas">
            <x-tables.table variant="spreadsheet" caption="Histórico de entradas" :columns="$entryColumns" :rows="$entryRows" empty-message="Nenhuma entrada encontrada." />
            <div class="movement-table-footer"><span class="movement-table-footer__summary" data-movement-summary></span><nav class="movement-pagination" aria-label="Paginação das entradas"><button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button><span class="movement-pagination__current" data-page-current aria-live="polite">1 / 1</span><button class="btn btn-secondary" type="button" data-page-next disabled>Próximo <x-icons.icon name="caret-right" size="sm" /></button></nav></div>
        </div>
        <x-slot:footer><p class="movement-demo-note">Dados carregados do banco de dados.</p></x-slot:footer>
    </x-cards.panel>
    <x-movements.record-dialogs entity="entrada" create-title="Nova entrada" description="Informe os dados do produto recebido e da movimentação." :fields="$entryFormFields" :action="route('entrance.store')" />
</x-layouts.app>
