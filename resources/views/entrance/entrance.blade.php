<x-layouts.app title="Entradas · JEMP" page="entrance">
    @php
        $entryRows = $movements->map(fn ($movement) => [
            'id' => $movement->id,
            'code' => $movement->code,
            'date' => $movement->occurred_at->format('d/m/Y H:i'),
            'type' => $movement->type,
            'product' => $movement->product->name,
            'quantity' => $movement->quantity,
            'origin' => $movement->origin,
            'destination' => $movement->destination,
            'justification' => $movement->notes,
            '_data' => ['type' => $movement->type, 'origin' => $movement->origin],
        ])->all();
        $entryColumns = [
            ['key' => 'code', 'label' => 'Cód.'],
            ['key' => 'date', 'label' => 'Data'],
            ['key' => 'type', 'label' => 'Tipo'],
            ['key' => 'product', 'label' => 'Produto'],
            ['key' => 'quantity', 'label' => 'Qtd'],
            ['key' => 'origin', 'label' => 'Origem'],
            ['key' => 'destination', 'label' => 'Destino'],
            ['key' => 'justification', 'label' => 'Justificação'],
            ['key' => 'code', 'label' => 'Ações', 'type' => 'actions', 'actions' => ['view'], 'class' => 'movement-actions-column'],
        ];
        $productOptions = $products->mapWithKeys(fn ($product) => [$product->id => $product->code.' — '.$product->name])->all();
        $entryFormFields = [
            ['name' => 'date', 'label' => 'Data e hora', 'type' => 'datetime-local', 'required' => true],
            ['name' => 'type', 'label' => 'Tipo', 'type' => 'select', 'required' => true, 'options' => ['Compra' => 'Compra', 'Transferência' => 'Transferência', 'Devolução' => 'Devolução']],
            ['name' => 'product_id', 'label' => 'Produto', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um produto'] + $productOptions],
            ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'origin', 'label' => 'Origem', 'required' => true],
            ['name' => 'destination', 'label' => 'Destino', 'type' => 'select', 'required' => true, 'options' => ['' => 'Selecione um local'] + $locations],
            ['name' => 'justification', 'label' => 'Justificação', 'type' => 'textarea'],
        ];
    @endphp

    <div class="page-head movement-page__head"><div><h1>Entradas</h1></div></div>
    @if (session('status')) <p class="movement-feedback" role="status">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p class="movement-feedback movement-feedback--error" role="alert">{{ $errors->first() }}</p> @endif

    <x-cards.panel class="movement-panel movement-panel--entries" variant="mint" title="Listagem de Entradas" description="Gerencie as entradas e acompanhe o estoque atualizado.">
        <x-slot:actions>
            <button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova entrada">
                <x-icons.icon name="plus" size="sm" color="white" /> Nova Entrada
            </button>
        </x-slot:actions>
        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar entradas" data-movement-filter-form>
                <div class="movement-search"><x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto ou documento..." aria-label="Buscar entradas" data-movement-search><x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading></x-inputs.input></div>
                <x-inputs.input name="type" label="Tipo" type="select" size="sm" :options="['' => 'Todos os tipos', 'Compra' => 'Compra', 'Transferência' => 'Transferência', 'Devolução' => 'Devolução']" data-movement-filter="type" />
                <x-inputs.input name="origin" label="Origem" type="select" size="sm" :options="['' => 'Todas as origens'] + $locations" data-movement-filter="origin" />
            </x-filters.filter-bar>
        </x-slot:filters>
        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="entradas">
            <x-tables.table variant="spreadsheet" caption="Histórico de entradas" :columns="$entryColumns" :rows="$entryRows" empty-message="Nenhuma entrada encontrada." />
            <div class="movement-table-footer"><span class="movement-table-footer__summary" data-movement-summary></span><nav class="movement-pagination" aria-label="Paginação das entradas"><button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button><span class="movement-pagination__current" data-page-current aria-live="polite">1 / 1</span><button class="btn btn-secondary" type="button" data-page-next disabled>Próximo <x-icons.icon name="caret-right" size="sm" /></button></nav></div>
        </div>
    </x-cards.panel>
    <x-movements.record-dialogs entity="entrada" create-title="Nova entrada" description="Informe os dados do produto recebido e da movimentação." :fields="$entryFormFields" :action="route('entrance.store')" />
</x-layouts.app>
