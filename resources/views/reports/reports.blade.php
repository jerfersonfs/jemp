@php
    $inventoryRows = [
        ['code' => '0001', 'product' => 'Palete PBR', 'category' => 'Palete', 'quantity' => '1.920', 'minimum' => '500', 'unit' => 'un.', 'status' => 'Normal', 'preset' => 'normal'],
        ['code' => '0002', 'product' => 'Palete FP', 'category' => 'Palete', 'quantity' => '1', 'minimum' => '100', 'unit' => 'un.', 'status' => 'Zerado', 'preset' => 'zero'],
        ['code' => '0003', 'product' => 'Caixa organizadora', 'category' => 'Organização', 'quantity' => '18', 'minimum' => '20', 'unit' => 'un.', 'status' => 'Estoque baixo', 'preset' => 'low'],
        ['code' => '0004', 'product' => 'Filme stretch', 'category' => 'Embalagem', 'quantity' => '340', 'minimum' => '80', 'unit' => 'un.', 'status' => 'Normal', 'preset' => 'normal'],
        ['code' => '0005', 'product' => 'Fita adesiva', 'category' => 'Embalagem', 'quantity' => '12', 'minimum' => '30', 'unit' => 'un.', 'status' => 'Estoque baixo', 'preset' => 'low'],
        ['code' => '0006', 'product' => 'Bobina térmica', 'category' => 'Suprimentos', 'quantity' => '0', 'minimum' => '40', 'unit' => 'un.', 'status' => 'Zerado', 'preset' => 'zero'],
    ];

    $invoiceRows = [
        ['number' => '000184', 'series' => '3', 'key' => '3526…1840', 'date' => '15/09/2026', 'party' => 'Fornecedor Central', 'value' => 'R$ 8.450,00', 'operation' => 'Entrada', 'status' => 'Autorizada', 'preset' => 'authorized'],
        ['number' => '000183', 'series' => '3', 'key' => '3526…1833', 'date' => '14/09/2026', 'party' => 'Palete PBR Ltda.', 'value' => 'R$ 1.920,00', 'operation' => 'Saída', 'status' => 'Pendente', 'preset' => 'pending'],
        ['number' => '000182', 'series' => '1', 'key' => '3526…1826', 'date' => '12/09/2026', 'party' => 'Embalagens Sul', 'value' => 'R$ 3.680,00', 'operation' => 'Entrada', 'status' => 'Autorizada', 'preset' => 'authorized'],
        ['number' => '000181', 'series' => '1', 'key' => '3526…1819', 'date' => '10/09/2026', 'party' => 'Comercial Horizonte', 'value' => 'R$ 620,00', 'operation' => 'Saída', 'status' => 'Cancelada', 'preset' => 'canceled'],
        ['number' => '000180', 'series' => '2', 'key' => '3526…1802', 'date' => '08/09/2026', 'party' => 'Distribuidora União', 'value' => 'R$ 5.250,00', 'operation' => 'Entrada', 'status' => 'Pendente', 'preset' => 'pending'],
    ];

    $movementRows = [
        ['product' => 'Palete PBR', 'type' => 'Entrada', 'quantity' => '200 un.', 'date' => '15/09/2026 10:30', 'responsible' => 'Marina Pereira', 'document' => 'NF-e 000184', 'preset' => 'entry'],
        ['product' => 'Filme stretch', 'type' => 'Saída', 'quantity' => '24 un.', 'date' => '15/09/2026 09:42', 'responsible' => 'João Costa', 'document' => 'Pedido 00476', 'preset' => 'exit'],
        ['product' => 'Palete FP', 'type' => 'Interna', 'quantity' => '12 un.', 'date' => '14/09/2026 16:18', 'responsible' => 'Marina Pereira', 'document' => '—', 'preset' => 'internal'],
        ['product' => 'Caixa organizadora', 'type' => 'Entrada', 'quantity' => '40 un.', 'date' => '12/09/2026 11:05', 'responsible' => 'Rafael Lima', 'document' => 'NF-e 000182', 'preset' => 'entry'],
        ['product' => 'Fita adesiva', 'type' => 'Saída', 'quantity' => '18 un.', 'date' => '11/09/2026 14:27', 'responsible' => 'João Costa', 'document' => 'Pedido 00471', 'preset' => 'exit'],
    ];

    $auditRows = [
        ['user' => 'Marina Pereira', 'action' => 'Atualização', 'module' => 'Estoque · Palete PBR', 'date' => '15/09/2026 10:31', 'description' => 'Quantidade alterada de 1.720 para 1.920 un.', 'preset' => 'update'],
        ['user' => 'João Costa', 'action' => 'Registro criado', 'module' => 'Saídas · Filme stretch', 'date' => '15/09/2026 09:42', 'description' => 'Saída de 24 un. vinculada ao pedido 00476.', 'preset' => 'created'],
        ['user' => 'Rafael Lima', 'action' => 'Consulta', 'module' => 'Notas fiscais · 000182', 'date' => '14/09/2026 16:20', 'description' => 'Documento fiscal consultado.', 'preset' => 'read'],
        ['user' => 'Marina Pereira', 'action' => 'Atualização', 'module' => 'Produtos · Fita adesiva', 'date' => '12/09/2026 11:07', 'description' => 'Estoque mínimo alterado de 20 para 30 un.', 'preset' => 'update'],
        ['user' => 'Rafael Lima', 'action' => 'Registro criado', 'module' => 'Entradas · Caixa organizadora', 'date' => '12/09/2026 11:05', 'description' => 'Entrada de 40 un. registrada.', 'preset' => 'created'],
    ];

    $reportSections = [
        'overview' => [
            'label' => 'Visão Geral', 'heading' => 'Visão geral da operação', 'description' => 'Indicadores consolidados de estoque e movimentações.', 'rows' => $inventoryRows,
            'presets' => [['value' => 'all', 'label' => 'Todos os produtos'], ['value' => 'low', 'label' => 'Estoque baixo'], ['value' => 'zero', 'label' => 'Zerados']],
            'columns' => [['key' => 'code', 'label' => 'Código'], ['key' => 'product', 'label' => 'Produto'], ['key' => 'category', 'label' => 'Categoria'], ['key' => 'quantity', 'label' => 'Qtd. atual'], ['key' => 'minimum', 'label' => 'Qtd. mínima'], ['key' => 'unit', 'label' => 'Unidade'], ['key' => 'status', 'label' => 'Situação', 'type' => 'status'], ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver']],
            'statusVariants' => ['Normal' => 'success', 'Estoque baixo' => 'warning', 'Zerado' => 'danger'],
            'metrics' => [
                ['label' => 'Produtos cadastrados', 'value' => '248', 'source' => 'overview'], ['label' => 'Unidades em estoque', 'value' => '4.820', 'source' => 'overview'], ['label' => 'Estoque baixo', 'value' => '12 produtos', 'source' => 'low'], ['label' => 'Entradas no período', 'value' => '86', 'source' => 'movement-entry'], ['label' => 'Saídas no período', 'value' => '64', 'source' => 'movement-exit'],
            ],
        ],
        'invoices' => [
            'label' => 'Notas Fiscais', 'heading' => 'Notas fiscais', 'description' => 'Consulte emissões, valores, situação e referências dos documentos fiscais.', 'rows' => $invoiceRows,
            'presets' => [['value' => 'all', 'label' => 'Todas as notas'], ['value' => 'pending', 'label' => 'Pendentes'], ['value' => 'authorized', 'label' => 'Autorizadas'], ['value' => 'canceled', 'label' => 'Canceladas']],
            'columns' => [['key' => 'number', 'label' => 'Número'], ['key' => 'series', 'label' => 'Série'], ['key' => 'key', 'label' => 'Chave de acesso'], ['key' => 'date', 'label' => 'Emissão'], ['key' => 'party', 'label' => 'Fornecedor / Cliente'], ['key' => 'value', 'label' => 'Valor'], ['key' => 'operation', 'label' => 'Operação'], ['key' => 'status', 'label' => 'Situação', 'type' => 'status'], ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver']],
            'statusVariants' => ['Autorizada' => 'success', 'Pendente' => 'warning', 'Cancelada' => 'danger'],
            'metrics' => [
                ['label' => 'Notas emitidas', 'value' => '128', 'source' => 'invoices'], ['label' => 'Valor total', 'value' => 'R$ 84.250', 'source' => 'invoices'], ['label' => 'Pendentes', 'value' => '8 notas', 'source' => 'pending'], ['label' => 'Entradas fiscais', 'value' => '72 notas', 'source' => 'invoice-entry'], ['label' => 'Saídas fiscais', 'value' => '56 notas', 'source' => 'invoice-exit'],
            ],
        ],
        'stock' => [
            'label' => 'Estoque', 'heading' => 'Estoque atual', 'description' => 'Posição dos produtos, mínimos cadastrados e situação de cada item.', 'rows' => $inventoryRows,
            'presets' => [['value' => 'all', 'label' => 'Todos os produtos'], ['value' => 'low', 'label' => 'Estoque baixo'], ['value' => 'zero', 'label' => 'Zerados'], ['value' => 'normal', 'label' => 'Normais']],
            'columns' => [['key' => 'code', 'label' => 'Código'], ['key' => 'product', 'label' => 'Produto'], ['key' => 'category', 'label' => 'Categoria'], ['key' => 'quantity', 'label' => 'Qtd. atual'], ['key' => 'minimum', 'label' => 'Qtd. mínima'], ['key' => 'unit', 'label' => 'Unidade'], ['key' => 'status', 'label' => 'Situação', 'type' => 'status'], ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver']],
            'statusVariants' => ['Normal' => 'success', 'Estoque baixo' => 'warning', 'Zerado' => 'danger'],
            'metrics' => [
                ['label' => 'Produtos cadastrados', 'value' => '248', 'source' => 'stock'], ['label' => 'Unidades em estoque', 'value' => '4.820', 'source' => 'stock'], ['label' => 'Estoque baixo', 'value' => '12 produtos', 'source' => 'low'], ['label' => 'Produtos zerados', 'value' => '4 produtos', 'source' => 'zero'], ['label' => 'Categorias', 'value' => '18', 'source' => 'stock'],
            ],
        ],
        'movements' => [
            'label' => 'Movimentações', 'heading' => 'Movimentações no período', 'description' => 'Entradas, saídas e transferências com responsável e documento associado.', 'rows' => $movementRows,
            'presets' => [['value' => 'all', 'label' => 'Todas as movimentações'], ['value' => 'entry', 'label' => 'Entradas'], ['value' => 'exit', 'label' => 'Saídas'], ['value' => 'internal', 'label' => 'Internas']],
            'columns' => [['key' => 'product', 'label' => 'Produto'], ['key' => 'type', 'label' => 'Tipo'], ['key' => 'quantity', 'label' => 'Quantidade'], ['key' => 'date', 'label' => 'Data e hora'], ['key' => 'responsible', 'label' => 'Responsável'], ['key' => 'document', 'label' => 'Documento'], ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver']],
            'statusVariants' => [],
            'metrics' => [
                ['label' => 'Movimentações', 'value' => '150', 'source' => 'movements'], ['label' => 'Itens recebidos', 'value' => '1.240 un.', 'source' => 'entry'], ['label' => 'Itens expedidos', 'value' => '860 un.', 'source' => 'exit'], ['label' => 'Transferências', 'value' => '22', 'source' => 'internal'], ['label' => 'Responsáveis ativos', 'value' => '8', 'source' => 'movements'],
            ],
        ],
        'audit' => [
            'label' => 'Auditoria', 'heading' => 'Auditoria do sistema', 'description' => 'Ações registradas por usuário, módulo e horário.', 'rows' => $auditRows,
            'presets' => [['value' => 'all', 'label' => 'Todas as ações'], ['value' => 'created', 'label' => 'Registros criados'], ['value' => 'update', 'label' => 'Atualizações'], ['value' => 'read', 'label' => 'Consultas']],
            'columns' => [['key' => 'user', 'label' => 'Usuário'], ['key' => 'action', 'label' => 'Ação'], ['key' => 'module', 'label' => 'Módulo / registro'], ['key' => 'date', 'label' => 'Data e hora'], ['key' => 'description', 'label' => 'Descrição'], ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver']],
            'statusVariants' => [],
            'metrics' => [
                ['label' => 'Ações registradas', 'value' => '1.284', 'source' => 'audit'], ['label' => 'Usuários ativos', 'value' => '8', 'source' => 'audit'], ['label' => 'Atualizações', 'value' => '326', 'source' => 'update'], ['label' => 'Registros criados', 'value' => '184', 'source' => 'created'], ['label' => 'Consultas', 'value' => '774', 'source' => 'read'],
            ],
        ],
    ];

    foreach ($reportSections as $sectionKey => $section) {
        foreach ($section['rows'] as $rowIndex => $row) {
            $reportSections[$sectionKey]['rows'][$rowIndex]['detailTarget'] = 'report-record-details';
        }
    }
@endphp

<x-layouts.app title="Relatórios · JEMP" page="reports">
    <section class="reports-page" data-reports-page data-report-config="{{ Illuminate\Support\Js::encode($reportSections) }}">
        <header class="reports-page__heading">
            <h1>Relatórios</h1>
        </header>

        <div class="reports-workspace">
            <aside class="reports-workspace__filters" aria-label="Filtros dos relatórios">
                <x-filters.vertical-filter
                    name="preset"
                    label="Filtros pré-gerados"
                    search-label="Buscar"
                    search-placeholder="Buscar registros"
                    :filters="$reportSections['overview']['presets']"
                    selected="all"
                    data-report-filter-form>
                    <x-buttons.button type="submit" variant="secondary">Aplicar filtros</x-buttons.button>
                </x-filters.vertical-filter>
            </aside>

            <div class="reports-workspace__content">
                <x-tabs.tabs id="reports-tabs" label="Seções dos relatórios" variant="pills" :tabs="collect($reportSections)->map(fn ($section, $key) => ['id' => $key, 'label' => $section['label']])->values()->all()">
                    @foreach ($reportSections as $sectionKey => $section)
                        <section class="reports-panel" data-tab-panel="{{ $sectionKey }}" hidden>
                            <header class="reports-panel__intro">
                                <h2 data-report-heading>{{ $section['heading'] }}</h2>
                                <p data-report-description>{{ $section['description'] }}</p>
                            </header>
                            <div class="reports-panel__metrics" data-report-metrics>
                                @foreach ($section['metrics'] as $metricIndex => $metric)
                                    <x-cards.stat-card
                                        :label="$metric['label']"
                                        :value="$metric['value']"
                                        :size="$metricIndex === 0 ? 'large' : 'regular'"
                                        :variant="$metricIndex === 0 ? 'default' : 'mint'"
                                        details-target="report-indicator-details"
                                        data-report-metric="{{ $metricIndex }}"
                                        data-metric-source="{{ $metric['source'] }}" />
                                @endforeach
                            </div>
                            <div class="reports-panel__table">
                                <x-tables.table
                                    variant="rounded"
                                    :columns="$section['columns']"
                                    :rows="$section['rows']"
                                    :status-variants="$section['statusVariants']"
                                    :caption="'Relatório: '.$section['label']"
                                    data-report-table />
                                <footer class="reports-pagination" data-report-pagination>
                                    <p data-report-summary></p>
                                    <nav aria-label="Paginação do relatório">
                                        <button type="button" data-report-prev aria-label="Página anterior"><i class="ph ph-caret-left" aria-hidden="true"></i> Anterior</button>
                                        <span data-report-page aria-live="polite"></span>
                                        <button type="button" data-report-next aria-label="Próxima página">Próximo <i class="ph ph-caret-right" aria-hidden="true"></i></button>
                                    </nav>
                                </footer>
                            </div>
                        </section>
                    @endforeach
                </x-tabs.tabs>
            </div>
        </div>
        <p class="reports-page__note">Os dados exibidos são demonstrativos e ilustram as informações previstas para as views SQL.</p>
    </section>

    <div class="reports-dialog-layer" data-dialog-layer="report-indicator-details" role="presentation" hidden>
        <x-dialogs.details-dialog id="report-indicator-dialog" title="Composição do indicador" description="Registros que compõem este indicador." variant="indicator" :columns="$reportSections['overview']['columns']" :rows="$reportSections['overview']['rows']" :status-variants="$reportSections['overview']['statusVariants']" />
    </div>
    <div class="reports-dialog-layer" data-dialog-layer="report-record-details" role="presentation" hidden>
        <x-dialogs.details-dialog id="report-record-dialog" title="Detalhes do registro" description="Informações completas do registro selecionado." :details="[['label' => 'Registro', 'value' => 'Selecione uma linha da tabela.']]" />
    </div>
</x-layouts.app>
