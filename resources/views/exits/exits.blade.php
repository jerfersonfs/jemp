<x-layouts.app title="Saídas · JEMP" page="exits">
    @php
    $exitItems = [
    ['code' => '000001', 'date' => '26/09/2026', 'type' => 'Consumo interno', 'product' => 'Produto A', 'lot' => 'LT0001', 'unit' => 'UN', 'stock' => '150', 'quantity' => '10', 'remaining' => '140', 'warehouse' => 'Depósito principal', 'recipient' => 'Operação interna', 'reference' => 'REQ-1042', 'responsible' => 'Maria Paula', '_data' => ['type' => 'Consumo interno', 'warehouse' => 'Depósito principal']],
    ['code' => '000002', 'date' => '25/09/2026', 'type' => 'Venda', 'product' => 'Produto B', 'lot' => 'LT0002', 'unit' => 'UN', 'stock' => '80', 'quantity' => '5', 'remaining' => '75', 'warehouse' => 'Barracão 1', 'recipient' => 'Cliente Aurora', 'reference' => 'NF-4521', 'responsible' => 'Pedro Duarte', '_data' => ['type' => 'Venda', 'warehouse' => 'Barracão 1']],
    ['code' => '000003', 'date' => '24/09/2026', 'type' => 'Avaria', 'product' => 'Produto C', 'lot' => 'LT0003', 'unit' => 'CX', 'stock' => '200', 'quantity' => '20', 'remaining' => '180', 'warehouse' => 'Barracão 2', 'recipient' => 'Operação interna', 'reference' => 'OC-077', 'responsible' => 'Maria Paula', '_data' => ['type' => 'Avaria', 'warehouse' => 'Barracão 2']],
    ['code' => '000004', 'date' => '23/09/2026', 'type' => 'Consumo interno', 'product' => 'Produto D', 'lot' => 'LT0004', 'unit' => 'UN', 'stock' => '45', 'quantity' => '3', 'remaining' => '42', 'warehouse' => 'Depósito principal', 'recipient' => 'Logística', 'reference' => 'REQ-1038', 'responsible' => 'Pedro Duarte', '_data' => ['type' => 'Consumo interno', 'warehouse' => 'Depósito principal']],
    ['code' => '000005', 'date' => '22/09/2026', 'type' => 'Venda', 'product' => 'Palete PBR', 'lot' => 'LT0140', 'unit' => 'UN', 'stock' => '72', 'quantity' => '12', 'remaining' => '60', 'warehouse' => 'Barracão 1', 'recipient' => 'Cliente Horizonte', 'reference' => 'NF-4518', 'responsible' => 'Maria Paula', '_data' => ['type' => 'Venda', 'warehouse' => 'Barracão 1']],
    ['code' => '000006', 'date' => '21/09/2026', 'type' => 'Ajuste de estoque', 'product' => 'Filme stretch', 'lot' => 'LT0210', 'unit' => 'RL', 'stock' => '34', 'quantity' => '2', 'remaining' => '32', 'warehouse' => 'Barracão 2', 'recipient' => 'Operação interna', 'reference' => 'AJ-0091', 'responsible' => 'Pedro Duarte', '_data' => ['type' => 'Ajuste de estoque', 'warehouse' => 'Barracão 2']],
    ['code' => '000007', 'date' => '20/09/2026', 'type' => 'Venda', 'product' => 'Fita adesiva', 'lot' => 'LT0305', 'unit' => 'CX', 'stock' => '96', 'quantity' => '8', 'remaining' => '88', 'warehouse' => 'Depósito principal', 'recipient' => 'Cliente Aurora', 'reference' => 'NF-4513', 'responsible' => 'Maria Paula', '_data' => ['type' => 'Venda', 'warehouse' => 'Depósito principal']],
    ['code' => '000008', 'date' => '19/09/2026', 'type' => 'Avaria', 'product' => 'Caixa organizadora', 'lot' => 'LT0420', 'unit' => 'UN', 'stock' => '55', 'quantity' => '4', 'remaining' => '51', 'warehouse' => 'Barracão 1', 'recipient' => 'Operação interna', 'reference' => 'OC-073', 'responsible' => 'Pedro Duarte', '_data' => ['type' => 'Avaria', 'warehouse' => 'Barracão 1']],
    ['code' => '000009', 'date' => '18/09/2026', 'type' => 'Consumo interno', 'product' => 'Produto A', 'lot' => 'LT0001', 'unit' => 'UN', 'stock' => '140', 'quantity' => '15', 'remaining' => '125', 'warehouse' => 'Depósito principal', 'recipient' => 'Operação interna', 'reference' => 'REQ-1029', 'responsible' => 'Maria Paula', '_data' => ['type' => 'Consumo interno', 'warehouse' => 'Depósito principal']],
    ['code' => '000010', 'date' => '17/09/2026', 'type' => 'Venda', 'product' => 'Produto B', 'lot' => 'LT0002', 'unit' => 'UN', 'stock' => '75', 'quantity' => '9', 'remaining' => '66', 'warehouse' => 'Barracão 1', 'recipient' => 'Cliente Horizonte', 'reference' => 'NF-4504', 'responsible' => 'Pedro Duarte', '_data' => ['type' => 'Venda', 'warehouse' => 'Barracão 1']],
    ['code' => '000011', 'date' => '16/09/2026', 'type' => 'Ajuste de estoque', 'product' => 'Produto C', 'lot' => 'LT0003', 'unit' => 'CX', 'stock' => '180', 'quantity' => '6', 'remaining' => '174', 'warehouse' => 'Barracão 2', 'recipient' => 'Operação interna', 'reference' => 'AJ-0088', 'responsible' => 'Maria Paula', '_data' => ['type' => 'Ajuste de estoque', 'warehouse' => 'Barracão 2']],
    ['code' => '000012', 'date' => '15/09/2026', 'type' => 'Consumo interno', 'product' => 'Produto D', 'lot' => 'LT0004', 'unit' => 'UN', 'stock' => '42', 'quantity' => '7', 'remaining' => '35', 'warehouse' => 'Depósito principal', 'recipient' => 'Logística', 'reference' => 'REQ-1018', 'responsible' => 'Pedro Duarte', '_data' => ['type' => 'Consumo interno', 'warehouse' => 'Depósito principal']],
    ];
    $exitColumns = [
    ['key' => 'code', 'label' => 'Código'],
    ['key' => 'date', 'label' => 'Data'],
    ['key' => 'type', 'label' => 'Tipo'],
    ['key' => 'product', 'label' => 'Produto'],
    ['key' => 'quantity', 'label' => 'Qtd. saída'],
    ['key' => 'warehouse', 'label' => 'Almoxarifado'],
    ['key' => 'recipient', 'label' => 'Destinatário'],
    ['key' => 'code', 'label' => 'Ações', 'type' => 'actions', 'actions' => ['view', 'edit', 'delete'], 'class' => 'movement-actions-column'],
    ];
    $exitFormFields = [
    ['name' => 'date', 'label' => 'Data da saída', 'type' => 'date', 'required' => true],
    ['name' => 'type', 'label' => 'Tipo de saída', 'type' => 'select', 'required' => true, 'options' => ['Consumo interno' => 'Consumo interno', 'Avaria' => 'Avaria', 'Venda' => 'Venda', 'Ajuste de estoque' => 'Ajuste de estoque']],
    ['name' => 'product', 'label' => 'Produto', 'required' => true],
    ['name' => 'lot', 'label' => 'Lote'],
    ['name' => 'unit', 'label' => 'Unidade'],
    ['name' => 'stock', 'label' => 'Estoque atual', 'type' => 'number'],
    ['name' => 'quantity', 'label' => 'Quantidade de saída', 'type' => 'number', 'required' => true],
    ['name' => 'warehouse', 'label' => 'Almoxarifado', 'required' => true],
    ['name' => 'recipient', 'label' => 'Destinatário'],
    ['name' => 'reference', 'label' => 'Documento de referência'],
    ['name' => 'responsible', 'label' => 'Responsável'],
    ['name' => 'notes', 'label' => 'Observações', 'type' => 'textarea'],
    ];
    @endphp

    <div class="page-head movement-page__head">
        <div>
            <h1>Saídas</h1>
        </div>
    </div>

    <x-cards.panel class="exit-panel movement-panel movement-panel--exits" variant="mint" title="Histórico de Saídas" description="Acompanhe e gerencie as saídas registradas no estoque.">
        <x-slot:actions>
            <button class="btn btn-primary movement-action-button" type="button" data-dialog-open="movement-form" aria-label="Cadastrar nova saída">
                <x-icons.icon name="plus" size="sm" color="white" />
                Nova Saída
            </button>
        </x-slot:actions>

        <x-slot:filters>
            <x-filters.filter-bar class="movement-filter-bar" label="Filtrar saídas" data-movement-filter-form>
                <div class="movement-search">
                    <x-inputs.input name="search" type="search" size="sm" placeholder="Buscar produto ou documento..." aria-label="Buscar saídas" data-movement-search>
                        <x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading>
                    </x-inputs.input>
                </div>
                <x-inputs.input name="type" label="Tipo" type="select" size="sm" :options="['' => 'Todos os tipos', 'Consumo interno' => 'Consumo interno', 'Avaria' => 'Avaria', 'Venda' => 'Venda', 'Ajuste de estoque' => 'Ajuste de estoque']" data-movement-filter="type" />
                <x-inputs.input name="warehouse" label="Almoxarifado" type="select" size="sm" :options="['' => 'Todos os locais', 'Depósito principal' => 'Depósito principal', 'Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2']" data-movement-filter="warehouse" />
            </x-filters.filter-bar>
        </x-slot:filters>

        <div class="movement-table-wrap" data-movement-list data-page-size="10" data-resource-label="saídas">
            <x-tables.table variant="spreadsheet" caption="Histórico de saídas de estoque" :columns="$exitColumns" :rows="$exitItems" empty-message="Nenhuma saída encontrada." />
            <div class="movement-table-footer">
                <span class="movement-table-footer__summary" data-movement-summary>Mostrando 1–10 de 12 saídas demonstrativas</span>
                <nav class="movement-pagination" aria-label="Paginação das saídas">
                    <button class="btn btn-secondary" type="button" data-page-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button>
                    <span class="movement-pagination__current" data-page-current aria-live="polite">1 / 2</span>
                    <button class="btn btn-secondary" type="button" data-page-next>Próximo <x-icons.icon name="caret-right" size="sm" /></button>
                </nav>
            </div>
        </div>
        <x-slot:footer>
            <p class="movement-demo-note">Dados ilustrativos para demonstração. Alterações feitas nos dialogs ficam somente nesta página.</p>
        </x-slot:footer>
    </x-cards.panel>

    <x-movements.record-dialogs entity="saída" create-title="Nova saída" description="Informe os dados da saída e do produto retirado do estoque." :fields="$exitFormFields" />
</x-layouts.app>