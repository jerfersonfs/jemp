<x-layouts.app title="Entradas · JEMP" page="entrance">
    @php
        $entries = [
            ['code' => '#1', 'date' => '15/09/2026 10:30', 'type' => 'Transferência', 'product' => 'Palete PBR', 'quantity' => '200 un.', 'origin' => 'Barracão 1', 'destination' => 'Depósito principal', 'justification' => 'Reposição de estoque', '_data' => ['type' => 'Transferência', 'origin' => 'Barracão 1']],
            ['code' => '#2', 'date' => '14/09/2026 16:15', 'type' => 'Compra', 'product' => 'Palete FP', 'quantity' => '80 un.', 'origin' => 'Fornecedor Central', 'destination' => 'Barracão 2', 'justification' => 'Pedido 00482', '_data' => ['type' => 'Compra', 'origin' => 'Fornecedor Central']],
            ['code' => '#3', 'date' => '13/09/2026 11:05', 'type' => 'Devolução', 'product' => 'Caixa organizadora', 'quantity' => '25 un.', 'origin' => 'Cliente Aurora', 'destination' => 'Depósito principal', 'justification' => 'Devolução de venda', '_data' => ['type' => 'Devolução', 'origin' => 'Cliente Aurora']],
            ['code' => '#4', 'date' => '12/09/2026 09:40', 'type' => 'Compra', 'product' => 'Filme stretch', 'quantity' => '120 un.', 'origin' => 'Embalagens Sul', 'destination' => 'Barracão 1', 'justification' => 'Pedido 00476', '_data' => ['type' => 'Compra', 'origin' => 'Embalagens Sul']],
            ['code' => '#5', 'date' => '11/09/2026 14:20', 'type' => 'Transferência', 'product' => 'Palete PBR', 'quantity' => '40 un.', 'origin' => 'Barracão 2', 'destination' => 'Depósito principal', 'justification' => 'Reorganização', '_data' => ['type' => 'Transferência', 'origin' => 'Barracão 2']],
            ['code' => '#6', 'date' => '10/09/2026 13:30', 'type' => 'Compra', 'product' => 'Fita adesiva', 'quantity' => '60 un.', 'origin' => 'Distribuidora Alfa', 'destination' => 'Barracão 2', 'justification' => 'Pedido 00463', '_data' => ['type' => 'Compra', 'origin' => 'Distribuidora Alfa']],
            ['code' => '#7', 'date' => '09/09/2026 08:55', 'type' => 'Devolução', 'product' => 'Palete FP', 'quantity' => '12 un.', 'origin' => 'Cliente Horizonte', 'destination' => 'Barracão 1', 'justification' => 'Troca autorizada', '_data' => ['type' => 'Devolução', 'origin' => 'Cliente Horizonte']],
            ['code' => '#8', 'date' => '08/09/2026 15:10', 'type' => 'Compra', 'product' => 'Caixa organizadora', 'quantity' => '100 un.', 'origin' => 'Plásticos Brasil', 'destination' => 'Depósito principal', 'justification' => 'Pedido 00451', '_data' => ['type' => 'Compra', 'origin' => 'Plásticos Brasil']],
            ['code' => '#9', 'date' => '07/09/2026 10:20', 'type' => 'Transferência', 'product' => 'Filme stretch', 'quantity' => '30 un.', 'origin' => 'Barracão 1', 'destination' => 'Barracão 2', 'justification' => 'Ajuste de estoque', '_data' => ['type' => 'Transferência', 'origin' => 'Barracão 1']],
            ['code' => '#10', 'date' => '06/09/2026 17:00', 'type' => 'Compra', 'product' => 'Fita adesiva', 'quantity' => '45 un.', 'origin' => 'Distribuidora Alfa', 'destination' => 'Depósito principal', 'justification' => 'Pedido 00438', '_data' => ['type' => 'Compra', 'origin' => 'Distribuidora Alfa']],
            ['code' => '#11', 'date' => '05/09/2026 12:35', 'type' => 'Compra', 'product' => 'Palete PBR', 'quantity' => '70 un.', 'origin' => 'Fornecedor Central', 'destination' => 'Barracão 1', 'justification' => 'Pedido 00430', '_data' => ['type' => 'Compra', 'origin' => 'Fornecedor Central']],
            ['code' => '#12', 'date' => '04/09/2026 09:15', 'type' => 'Transferência', 'product' => 'Caixa organizadora', 'quantity' => '18 un.', 'origin' => 'Barracão 2', 'destination' => 'Depósito principal', 'justification' => 'Reposição de estoque', '_data' => ['type' => 'Transferência', 'origin' => 'Barracão 2']],
        ];
        $entryColumns = [
            ['key' => 'code', 'label' => 'Cód.'],
            ['key' => 'date', 'label' => 'Data'],
            ['key' => 'type', 'label' => 'Tipo'],
            ['key' => 'product', 'label' => 'Entrada'],
            ['key' => 'quantity', 'label' => 'Qtd'],
            ['key' => 'origin', 'label' => 'Origem'],
            ['key' => 'destination', 'label' => 'Destino'],
            ['key' => 'justification', 'label' => 'Justificação'],
            ['key' => 'code', 'label' => 'Ações', 'type' => 'actions', 'actions' => ['view', 'edit', 'delete'], 'class' => 'movement-actions-column'],
        ];
        $entryFormFields = [
            ['name' => 'date', 'label' => 'Data e hora', 'type' => 'datetime-local', 'required' => true],
            ['name' => 'type', 'label' => 'Tipo', 'type' => 'select', 'required' => true, 'options' => ['Compra' => 'Compra', 'Transferência' => 'Transferência', 'Devolução' => 'Devolução']],
            ['name' => 'product', 'label' => 'Produto', 'required' => true],
            ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'required' => true],
            ['name' => 'origin', 'label' => 'Origem', 'required' => true],
            ['name' => 'destination', 'label' => 'Destino', 'required' => true],
            ['name' => 'justification', 'label' => 'Justificação', 'type' => 'textarea'],
        ];
    @endphp

    <div class="page-head movement-page__head">
        <div>
            <h1>Entradas</h1>
        </div>
    </div>

    <x-cards.panel class="movement-panel movement-panel--entries" variant="mint" title="Listagem de Entradas" description="Gerencie todas as entradas do seu catálogo.">
        <x-slot:actions>
            <button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova entrada">
                <x-icons.icon name="plus" size="sm" color="white" />
                Nova Entrada
            </button>
        </x-slot:actions>

        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar entradas" data-movement-filter-form>
                <div class="movement-search">
                    <x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto..." aria-label="Buscar entradas" data-movement-search>
                        <x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading>
                    </x-inputs.input>
                </div>
                <x-inputs.input name="type" label="Tipo" type="select" size="sm" :options="['' => 'Todos os tipos', 'Compra' => 'Compra', 'Transferência' => 'Transferência', 'Devolução' => 'Devolução']" data-movement-filter="type" />
                <x-inputs.input name="origin" label="Origem" type="select" size="sm" :options="['' => 'Todas as origens', 'Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2', 'Fornecedor Central' => 'Fornecedor Central', 'Cliente Aurora' => 'Cliente Aurora', 'Embalagens Sul' => 'Embalagens Sul', 'Cliente Horizonte' => 'Cliente Horizonte', 'Plásticos Brasil' => 'Plásticos Brasil', 'Distribuidora Alfa' => 'Distribuidora Alfa']" data-movement-filter="origin" />
            </x-filters.filter-bar>
        </x-slot:filters>

        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="entradas">
            <x-tables.table variant="spreadsheet" caption="Histórico de entradas" :columns="$entryColumns" :rows="$entries" empty-message="Nenhuma entrada encontrada." />
            <div class="movement-table-footer">
                <span class="movement-table-footer__summary" data-movement-summary>Mostrando 1–10 de 12 entradas demonstrativas</span>
                <nav class="movement-pagination" aria-label="Paginação das entradas">
                    <button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button>
                    <span class="movement-pagination__current" data-page-current aria-live="polite">1 / 2</span>
                    <button class="btn btn-secondary" type="button" data-page-next>Próximo <x-icons.icon name="caret-right" size="sm" /></button>
                </nav>
            </div>
        </div>
        <x-slot:footer><p class="movement-demo-note">Dados ilustrativos para demonstração visual; ainda não são persistidos no sistema.</p></x-slot:footer>
    </x-cards.panel>

    <x-movements.record-dialogs entity="entrada" create-title="Nova entrada" description="Informe os dados do produto recebido e da movimentação." :fields="$entryFormFields" />
</x-layouts.app>
