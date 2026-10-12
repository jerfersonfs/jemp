<x-layouts.app title="Movimentações internas · JEMP" page="internal">
    @php
        $transfers = [
            ['code' => '#1', 'product' => 'Palete PBR', 'quantity' => '50 un.', 'origin' => 'Barracão 1', 'destination' => 'Barracão 2', 'condition' => 'Novo', '_data' => ['origin' => 'Barracão 1', 'destination' => 'Barracão 2']],
            ['code' => '#2', 'product' => 'Palete FP', 'quantity' => '24 un.', 'origin' => 'Depósito principal', 'destination' => 'Barracão 1', 'condition' => 'Bom', '_data' => ['origin' => 'Depósito principal', 'destination' => 'Barracão 1']],
            ['code' => '#3', 'product' => 'Caixa organizadora', 'quantity' => '18 un.', 'origin' => 'Barracão 2', 'destination' => 'Depósito principal', 'condition' => 'Novo', '_data' => ['origin' => 'Barracão 2', 'destination' => 'Depósito principal']],
            ['code' => '#4', 'product' => 'Filme stretch', 'quantity' => '30 un.', 'origin' => 'Barracão 1', 'destination' => 'Barracão 2', 'condition' => 'Bom', '_data' => ['origin' => 'Barracão 1', 'destination' => 'Barracão 2']],
            ['code' => '#5', 'product' => 'Fita adesiva', 'quantity' => '12 un.', 'origin' => 'Depósito principal', 'destination' => 'Barracão 2', 'condition' => 'Novo', '_data' => ['origin' => 'Depósito principal', 'destination' => 'Barracão 2']],
            ['code' => '#6', 'product' => 'Palete PBR', 'quantity' => '16 un.', 'origin' => 'Barracão 2', 'destination' => 'Depósito principal', 'condition' => 'Revisado', '_data' => ['origin' => 'Barracão 2', 'destination' => 'Depósito principal']],
            ['code' => '#7', 'product' => 'Caixa organizadora', 'quantity' => '40 un.', 'origin' => 'Barracão 1', 'destination' => 'Depósito principal', 'condition' => 'Bom', '_data' => ['origin' => 'Barracão 1', 'destination' => 'Depósito principal']],
            ['code' => '#8', 'product' => 'Palete FP', 'quantity' => '8 un.', 'origin' => 'Depósito principal', 'destination' => 'Barracão 2', 'condition' => 'Novo', '_data' => ['origin' => 'Depósito principal', 'destination' => 'Barracão 2']],
            ['code' => '#9', 'product' => 'Filme stretch', 'quantity' => '22 un.', 'origin' => 'Barracão 2', 'destination' => 'Barracão 1', 'condition' => 'Bom', '_data' => ['origin' => 'Barracão 2', 'destination' => 'Barracão 1']],
            ['code' => '#10', 'product' => 'Fita adesiva', 'quantity' => '15 un.', 'origin' => 'Barracão 1', 'destination' => 'Depósito principal', 'condition' => 'Novo', '_data' => ['origin' => 'Barracão 1', 'destination' => 'Depósito principal']],
            ['code' => '#11', 'product' => 'Palete PBR', 'quantity' => '10 un.', 'origin' => 'Depósito principal', 'destination' => 'Barracão 1', 'condition' => 'Revisado', '_data' => ['origin' => 'Depósito principal', 'destination' => 'Barracão 1']],
            ['code' => '#12', 'product' => 'Caixa organizadora', 'quantity' => '6 un.', 'origin' => 'Barracão 2', 'destination' => 'Barracão 1', 'condition' => 'Bom', '_data' => ['origin' => 'Barracão 2', 'destination' => 'Barracão 1']],
        ];
        $transferColumns = [
            ['key' => 'code', 'label' => 'Cód.'],
            ['key' => 'product', 'label' => 'Nome do Produto'],
            ['key' => 'quantity', 'label' => 'Qtd'],
            ['key' => 'origin', 'label' => 'Origem'],
            ['key' => 'destination', 'label' => 'Destino'],
            ['key' => 'condition', 'label' => 'Condição'],
            ['key' => 'code', 'label' => 'Ações', 'type' => 'actions', 'actions' => ['view', 'edit', 'delete'], 'class' => 'movement-actions-column'],
        ];
        $transferFormFields = [
            ['name' => 'product', 'label' => 'Produto', 'required' => true],
            ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'required' => true],
            ['name' => 'origin', 'label' => 'Origem', 'required' => true],
            ['name' => 'destination', 'label' => 'Destino', 'required' => true],
            ['name' => 'condition', 'label' => 'Condição', 'type' => 'select', 'required' => true, 'options' => ['Novo' => 'Novo', 'Bom' => 'Bom', 'Revisado' => 'Revisado']],
        ];
    @endphp

    <div class="page-head movement-page__head">
        <div>
            <h1>Transferências</h1>
        </div>
    </div>

    <x-cards.panel class="movement-panel movement-panel--internal" variant="mint" title="Histórico de Movimentações" description="Gerencie todas as movimentações entre os armazéns.">
        <x-slot:actions>
            <button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova transferência">
                <x-icons.icon name="plus" size="sm" color="white" />
                Nova Transferência
            </button>
        </x-slot:actions>

        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar transferências" data-movement-filter-form>
                <div class="movement-search">
                    <x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto..." aria-label="Buscar movimentações" data-movement-search>
                        <x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading>
                    </x-inputs.input>
                </div>
                <x-inputs.input name="origin" label="Origem" type="select" size="sm" :options="['' => 'Todas as origens', 'Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2', 'Depósito principal' => 'Depósito principal']" data-movement-filter="origin" />
                <x-inputs.input name="destination" label="Destino" type="select" size="sm" :options="['' => 'Todos os destinos', 'Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2', 'Depósito principal' => 'Depósito principal']" data-movement-filter="destination" />
            </x-filters.filter-bar>
        </x-slot:filters>

        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="movimentações">
            <x-tables.table variant="spreadsheet" caption="Histórico de transferências internas" :columns="$transferColumns" :rows="$transfers" empty-message="Nenhuma movimentação encontrada." />
            <div class="movement-table-footer">
                <span class="movement-table-footer__summary" data-movement-summary>Mostrando 1–10 de 12 movimentações demonstrativas</span>
                <nav class="movement-pagination" aria-label="Paginação das transferências">
                    <button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button>
                    <span class="movement-pagination__current" data-page-current aria-live="polite">1 / 2</span>
                    <button class="btn btn-secondary" type="button" data-page-next>Próximo <x-icons.icon name="caret-right" size="sm" /></button>
                </nav>
            </div>
        </div>
        <x-slot:footer><p class="movement-demo-note">Dados ilustrativos para demonstração visual; ainda não são persistidos no sistema.</p></x-slot:footer>
    </x-cards.panel>

    <x-movements.record-dialogs entity="transferência" create-title="Nova transferência" description="Informe o produto, a quantidade e os armazéns de origem e destino." :fields="$transferFormFields" />
</x-layouts.app>
