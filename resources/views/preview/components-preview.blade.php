<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Componentes | JEMP</title>
    @vite(['resources/css/app.css', 'resources/css/preview/components-preview.css', 'resources/js/app.js', 'resources/js/preview/components-preview.js'])
</head>

<body>
    @php
    $invoiceColumns = [
    ['key' => 'number', 'label' => 'N° NF-e'],
    ['key' => 'customer', 'label' => 'Cliente'],
    ['key' => 'issue_date', 'label' => 'Emissão'],
    ['key' => 'due_date', 'label' => 'Vencimento'],
    ['key' => 'term', 'label' => 'Prazo'],
    ['key' => 'total', 'label' => 'Valor total'],
    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
    ['key' => 'detail_target', 'label' => 'Ações', 'type' => 'detail', 'targetKey' => 'detail_target', 'detailLabel' => 'Ver'],
    ];
    $invoiceRows = [
    ['number' => 'NF-4521', 'customer' => 'Indústria Penha', 'issue_date' => '10/09/2026', 'due_date' => '10/10/2026', 'term' => '10 dias', 'total' => 'R$ 8.900,00', 'status' => 'A Vencer', 'detail_target' => 'demo-invoice-NF-4521'],
    ['number' => 'NF-4522', 'customer' => 'Cristália', 'issue_date' => '10/09/2026', 'due_date' => '10/10/2026', 'term' => '10 dias', 'total' => 'R$ 4.250,00', 'status' => 'Vencido', 'detail_target' => 'demo-invoice-NF-4522'],
    ['number' => 'NF-4523', 'customer' => 'Jampac', 'issue_date' => '10/09/2026', 'due_date' => '10/10/2026', 'term' => '10 dias', 'total' => 'R$ 2.450,00', 'status' => 'Pago', 'detail_target' => 'demo-invoice-NF-4523'],
    ['number' => 'NF-4524', 'customer' => 'MAMÃ', 'issue_date' => '11/09/2026', 'due_date' => '11/10/2026', 'term' => '10 dias', 'total' => 'R$ 1.980,00', 'status' => 'A Vencer', 'detail_target' => 'demo-invoice-NF-4521'],
    ['number' => 'NF-4525', 'customer' => 'AQUI', 'issue_date' => '12/09/2026', 'due_date' => '12/10/2026', 'term' => '10 dias', 'total' => 'R$ 3.120,00', 'status' => 'Pago', 'detail_target' => 'demo-invoice-NF-4522'],
    ['number' => 'NF-4526', 'customer' => 'SCL', 'issue_date' => '12/09/2026', 'due_date' => '12/10/2026', 'term' => '10 dias', 'total' => 'R$ 990,00', 'status' => 'Vencido', 'detail_target' => 'demo-invoice-NF-4523'],
    ];
    $inventoryColumns = [
    ['key' => 'product', 'label' => 'Produto'],
    ['key' => 'category', 'label' => 'Categoria'],
    ['key' => 'measures', 'label' => 'Medidas'],
    ['key' => 'location', 'label' => 'Local'],
    ['key' => 'condition', 'label' => 'Condição'],
    ['key' => 'balance', 'label' => 'Saldo atual'],
    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
    ];
    $inventoryRows = [
    ['product' => 'Palete', 'category' => 'Palete', 'measures' => 'Palete', 'location' => 'Palete', 'condition' => 'Palete', 'balance' => 'Palete', 'status' => 'Baixo'],
    ['product' => 'Palete', 'category' => 'Palete', 'measures' => 'Palete', 'location' => 'Palete', 'condition' => 'Palete', 'balance' => 'Palete', 'status' => 'Baixo'],
    ['product' => 'Palete', 'category' => 'Palete', 'measures' => 'Palete', 'location' => 'Palete', 'condition' => 'Palete', 'balance' => 'Palete', 'status' => 'Baixo'],
    ];
    @endphp

    <main class="preview-page">
        <header class="preview-page__header">
            <h1>Preview dos componentes</h1>
            <p>Use os botões para abrir os dialogs. A sintaxe Blade de cada botão aparece no respectivo cartão.</p>
        </header>
        <section class="preview-page">
            <h2>Botões</h2>
        <x-buttons.button variant="primary">Teste butaum</x-buttons.button>
        </section>
        <section class="preview-section" aria-labelledby="existing-components-heading">
            <h2 id="existing-components-heading">Cards</h2>
            <div class="preview-existing-components">
                <x-cards.message-card variant="filled" trend="3,46%" />
                <x-cards.finance-card variant="pending" title="Faturas Pendentes" value="14" />
                <x-cards.finance-card variant="overdue" title="Total a Vencer" value="R$12.300,00" />
                <x-cards.stat-card size="regular" />
            </div>
        </section>

        <section class="preview-section" aria-labelledby="fields-tables-heading">
            <h2 id="fields-tables-heading">Campos, tabelas e abas</h2>
            <p class="preview-demo-note">Conteúdo de demonstração baseado nos exemplos do protótipo.</p>

            <x-filters.filter-bar action="#" label="Filtros de demonstração" data-preview-filter>
                <x-inputs.input name="preview-search" label="Pesquisar notas fiscais" type="search" placeholder="Número ou cliente" autocomplete="off">
                    <x-slot:leading><x-icons.icon name="magnifying-glass" color="black" /></x-slot:leading>
                </x-inputs.input>
                <x-inputs.input name="preview-category" label="Categoria" type="select" placeholder="Todas as categorias" :options="['PBR' => 'PBR', 'Descartável' => 'Descartável', 'Não Standard' => 'Não Standard']" />
                <x-inputs.input name="preview-warehouse" label="Barracão" type="select" :options="['service' => 'Base de Serviço/Reforma', 'pbr' => 'Estoque PBR', 'general' => 'Armazenamento Geral']" value="pbr" />
                <x-slot:actions>
                    <x-buttons.button type="button" variant="primary" data-dialog-open="demo-basic">
                        <x-icons.icon name="plus-circle" color="white" size="sm" />
                        Novo registro
                    </x-buttons.button>
                </x-slot:actions>
            </x-filters.filter-bar>
            <div class="preview-input-grid">
                <x-inputs.input name="preview-disabled" label="Campo desabilitado" value="Somente demonstração" disabled />
                <x-inputs.input name="preview-invalid" label="Campo com erro" value="" error="Exemplo de mensagem de validação." />
            </div>

            <div class="preview-tabs-surface">
                <x-tabs.tabs id="preview-data-tabs" label="Tabelas de demonstração" variant="pills" :tabs="[
                    ['id' => 'invoices', 'label' => 'Notas fiscais'],
                    ['id' => 'inventory', 'label' => 'Estoque'],
                ]">
                    <section class="preview-tab-panel" data-tab-panel="invoices">
                        <h3>Notas fiscais</h3>
                        <x-tables.table caption="Notas fiscais de demonstração" :columns="$invoiceColumns" :rows="$invoiceRows" :status-variants="['A Vencer' => 'warning', 'Vencido' => 'danger', 'Pago' => 'success']" variant="rounded" />
                    </section>
                    <section class="preview-tab-panel" data-tab-panel="inventory" hidden>
                        <h3>Estoque</h3>
                        <x-tables.table caption="Estoque de demonstração" :columns="$inventoryColumns" :rows="$inventoryRows" :status-variants="['Baixo' => 'danger']" />
                    </section>
                </x-tabs.tabs>
            </div>
        </section>

        <section class="preview-section" aria-labelledby="report-components-heading">
            <h2 id="report-components-heading">Relatórios: filtros, indicadores e variações de tabela</h2>
            <p class="preview-demo-note">Composição demonstrativa que reúne variantes presentes no protótipo; os dados são fictícios.</p>
            <div class="preview-report-layout">
                <aside class="preview-report-sidebar">
                    <x-filters.vertical-filter
                        label="Filtros pré-gerados"
                        name="report-status"
                        selected="all"
                        search-label="Buscar"
                        search-placeholder="Buscar nos registros"
                        :filters="[
                            ['value' => 'all', 'label' => 'Todos'],
                            ['value' => 'due', 'label' => 'A vencer'],
                            ['value' => 'overdue', 'label' => 'Vencidos'],
                        ]"
                        data-preview-filter>
                        <x-buttons.button type="submit" variant="secondary">Aplicar</x-buttons.button>
                    </x-filters.vertical-filter>
                </aside>
                <div class="preview-report-content">
                    <x-tabs.tabs id="preview-report-tabs" label="Seções do relatório" variant="pills" :tabs="[
                        ['id' => 'report-overview', 'label' => 'Visão Geral'],
                        ['id' => 'report-invoices', 'label' => 'Notas Fiscais'],
                        ['id' => 'report-stock', 'label' => 'Estoque'],
                        ['id' => 'report-movements', 'label' => 'Movimentações'],
                        ['id' => 'report-audit', 'label' => 'Auditoria'],
                    ]">
                        <section data-tab-panel="report-overview">
                            <div class="preview-report-metrics">
                                <x-cards.stat-card label="Total customers" value="2.420" size="large" details-target="demo-indicator-details" />
                                <x-cards.stat-card label="Notas a vencer" value="14" size="regular" details-target="demo-indicator-details" variant="mint"/>
                                <x-cards.stat-card label="Pagamentos no período" value="18" size="regular" details-target="demo-indicator-details" variant="mint"/>
                                <x-cards.stat-card label="Notas a vencer" value="14" size="regular" details-target="demo-indicator-details" variant="mint"/>
                                <x-cards.stat-card label="Pagamentos no período" value="18" size="regular" details-target="demo-indicator-details" variant="mint"/>
                            </div>
                        </section>
                        <section data-tab-panel="report-invoices" hidden><p>Conteúdo do relatório de notas fiscais.</p></section>
                        <section data-tab-panel="report-stock" hidden><p>Conteúdo do relatório de estoque.</p></section>
                        <section data-tab-panel="report-movements" hidden><p>Conteúdo do relatório de movimentações.</p></section>
                        <section data-tab-panel="report-audit" hidden><p>Conteúdo do relatório de auditoria.</p></section>
                    </x-tabs.tabs>
                </div>
            </div>

            <div class="preview-table-variants">
                <article class="preview-variant-card">
                    <h3>Tabela com filtros</h3>
                    <x-tables.table
                        variant="filtered"
                        data-preview-table
                        data-page-size="3"
                        caption="Notas fiscais filtráveis de demonstração"
                        :columns="$invoiceColumns"
                        :rows="$invoiceRows"
                        :status-variants="['A Vencer' => 'warning', 'Vencido' => 'danger', 'Pago' => 'success']">
                        <x-slot:filters>
                            <x-filters.filter-bar action="#" label="Filtrar notas fiscais" data-preview-filter data-preview-table-filter>
                                <x-inputs.input name="table-search" label="Buscar" type="search" placeholder="Número da nota ou cliente" />
                                <x-inputs.input name="table-status" label="Status" type="select" :options="['all' => 'Todos', 'paid' => 'Pagas', 'due' => 'A vencer']" />
                                <x-slot:actions><x-buttons.button type="submit" variant="primary">Filtrar</x-buttons.button></x-slot:actions>
                            </x-filters.filter-bar>
                        </x-slot:filters>
                        <x-slot:footer><div class="preview-table-pagination"><span data-pagination-summary>Mostrando 1–3 de 6 registros</span><nav aria-label="Paginação"><button type="button" data-page-prev disabled>Anterior</button><button type="button" data-page-current aria-current="page">1</button><button type="button" data-page-next>Próximo</button></nav></div></x-slot:footer>
                    </x-tables.table>
                </article>
                <article class="preview-variant-card">
                    <h3>Tabela em grade</h3>
                    <x-tables.table variant="spreadsheet" caption="Estoque em grade de demonstração" :columns="$inventoryColumns" :rows="$inventoryRows" :status-variants="['Baixo' => 'danger']" />
                </article>
            </div>

        </section>

        <section class="preview-section" aria-labelledby="popover-tooltip-heading">
            <h2 id="popover-tooltip-heading">Tooltip e popover</h2>
            <div class="preview-overlays">
                <div class="preview-overlay-example">
                    <h3>Tooltip</h3>
                    <x-tooltips.tooltip text="Adicionar ao cadastro" placement="top">Passe o mouse ou use Tab</x-tooltips.tooltip>
                </div>
                <div class="preview-overlay-example">
                    <h3>Popover de cadastro</h3>
                    <x-popovers.popover title="Cadastrar produto" description="Informe as características do produto." trigger-label="Cadastrar produto" size="wide">
                        <x-inputs.input name="popover-product-code" label="Código" value="001" readonly />
                        <x-inputs.input name="popover-product-name" label="Produto" placeholder="Nome do produto" />
                        <x-inputs.input name="popover-product-category" label="Categoria" type="select" :options="['pbr' => 'PBR', 'disposable' => 'Descartável', 'standard' => 'Não Standard']" />
                        <x-slot:actions><x-buttons.button type="button" variant="primary">Cadastrar</x-buttons.button></x-slot:actions>
                    </x-popovers.popover>
                </div>
            </div>
        </section>

        <section class="preview-section" aria-labelledby="dialogs-heading">
            <h2 id="dialogs-heading">Dialogs</h2>
            <div class="preview-dialog-grid">
                <article class="preview-dialog-card">
                    <div>
                        <h3>Dialog básico</h3>
                        <p>Estrutura com título, descrição, conteúdo e ações.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-basic"&gt;Abrir dialog&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-basic">Abrir dialog</button>
                </article>
                <article class="preview-dialog-card">
                    <div>
                        <h3>Confirmação</h3>
                        <p>Confirmação de ação destrutiva, como excluir um produto.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-confirmation"&gt;Excluir produto&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-confirmation">Excluir produto</button>
                </article>
                <article class="preview-dialog-card">
                    <div>
                        <h3>Cadastro de produto</h3>
                        <p>Campos para criar um produto.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-product-create"&gt;Cadastrar&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-product-create">Cadastrar produto</button>
                </article>
                <article class="preview-dialog-card">
                    <div>
                        <h3>Edição de produto</h3>
                        <p>O mesmo formulário aberto no modo de edição.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-product-edit"&gt;Editar&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-product-edit">Editar produto</button>
                </article>
                <article class="preview-dialog-card">
                    <div>
                        <h3>Perfil</h3>
                        <p>Atualização dos dados do usuário.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-profile"&gt;Editar perfil&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-profile">Editar perfil</button>
                </article>
                <article class="preview-dialog-card">
                    <div>
                        <h3>Abas e conteúdo</h3>
                        <p>Dialog básico composto com abas acessíveis.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-tabs"&gt;Abrir configurações&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-tabs">Abrir configurações</button>
                </article>
                <article class="preview-dialog-card">
                    <div>
                        <h3>Seletor de data</h3>
                        <p>Calendário mensal baseado no componente Date picker.</p>
                    </div>
                    <code>&lt;button type="button" data-dialog-open="demo-date-picker"&gt;Selecionar data&lt;/button&gt;</code>
                    <button type="button" data-dialog-open="demo-date-picker">Selecionar data</button>
                </article>
            </div>
        </section>

        <section class="preview-section" aria-labelledby="notices-heading">
            <h2 id="notices-heading">Notificações</h2>
            <div class="preview-notices">
                <x-dialogs.alert variant="warning" title="Operação cancelada" message="A alteração não foi aplicada." />
                <x-dialogs.alert variant="error" title="Erro na operação" message="Não foi possível concluir a solicitação." />
                <x-dialogs.alert variant="success" title="Operação concluída" message="O produto foi cadastrado com sucesso." />
                <x-dialogs.alert variant="info" title="Informação" message="Os dados do produto foram editados." />
                <x-dialogs.alert variant="bell" message="40 novas pendências para pagamento." />
            </div>
        </section>
    </main>

    <div class="preview-dialog-layer" data-dialog-layer="demo-basic" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.dialog title="Novo dialog" description="Use este componente para agrupar um conteúdo e suas ações." cancel-label="Voltar" submit-label="Continuar">
            <p>Inclua aqui o conteúdo necessário para esta etapa.</p>
        </x-dialogs.dialog>
    </div>
    <div class="preview-dialog-layer" data-dialog-layer="demo-confirmation" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.confirmation title="Excluir produto" message="Tem certeza de que deseja excluir este produto? Esta ação não poderá ser desfeita." confirm-label="Excluir produto" />
    </div>
    <div class="preview-dialog-layer" data-dialog-layer="demo-product-create" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.form-dialog
            title="Cadastrar produto"
            description="Informe os dados do produto para cadastrá-lo."
            submit-label="Cadastrar produto"
            :fields="[
                ['name' => 'code', 'label' => 'Código', 'type' => 'text', 'value' => '001', 'readonly' => true],
                ['name' => 'product', 'label' => 'Produto', 'type' => 'text', 'value' => 'Palete', 'required' => true],
                ['name' => 'type', 'label' => 'Tipo', 'type' => 'select', 'value' => 'Peça', 'options' => ['Peça' => 'Peça', 'Matéria-prima' => 'Matéria-prima', 'Componente' => 'Componente']],
                ['name' => 'width', 'label' => 'Largura', 'type' => 'text', 'value' => '1'],
                ['name' => 'length', 'label' => 'Comprimento', 'type' => 'text', 'value' => '1,20'],
                ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'value' => '10', 'required' => true],
                ['name' => 'location', 'label' => 'Local', 'type' => 'select', 'value' => 'Barracão 1', 'options' => ['Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2']],
            ]" />
    </div>
    <div class="preview-dialog-layer" data-dialog-layer="demo-product-edit" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.form-dialog
            mode="edit"
            title="Editar produto"
            description="Atualize os dados do produto e salve as alterações."
            submit-label="Salvar produto"
            method="PUT"
            :fields="[
                ['name' => 'code', 'label' => 'Código', 'type' => 'text', 'value' => '001', 'readonly' => true],
                ['name' => 'product', 'label' => 'Produto', 'type' => 'text', 'value' => 'Palete', 'required' => true],
                ['name' => 'type', 'label' => 'Tipo', 'type' => 'select', 'value' => 'Peça', 'options' => ['Peça' => 'Peça', 'Matéria-prima' => 'Matéria-prima', 'Componente' => 'Componente']],
                ['name' => 'width', 'label' => 'Largura', 'type' => 'text', 'value' => '1'],
                ['name' => 'length', 'label' => 'Comprimento', 'type' => 'text', 'value' => '1,20'],
                ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'value' => '10', 'required' => true],
                ['name' => 'location', 'label' => 'Local', 'type' => 'select', 'value' => 'Barracão 1', 'options' => ['Barracão 1' => 'Barracão 1', 'Barracão 2' => 'Barracão 2']],
            ]" />
    </div>
    <div class="preview-dialog-layer" data-dialog-layer="demo-profile" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.form-dialog
            mode="edit"
            title="Editar perfil"
            description="Atualize os dados do seu perfil. Salve quando terminar."
            submit-label="Salvar alterações"
            :fields="[
                ['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'value' => 'Pedro Duarte', 'autocomplete' => 'name'],
                ['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'value' => 'pedro@jemp.com.br', 'autocomplete' => 'email'],
                ['name' => 'username', 'label' => 'Usuário', 'type' => 'text', 'value' => '@peduarte', 'autocomplete' => 'username'],
            ]" />
    </div>
    <div class="preview-dialog-layer" data-dialog-layer="demo-tabs" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.dialog title="Configurações da conta" description="Gerencie o acesso e a senha da sua conta." :show-actions="false">
            <x-dialogs.tabbed-dialog id="preview-account" first-label="Acesso" second-label="Senha">
                <x-slot:second>
                    <p>Defina uma nova senha para proteger sua conta.</p>
                </x-slot:second>
                <p>Gerencie as permissões e os dados de acesso à sua conta.</p>
            </x-dialogs.tabbed-dialog>
        </x-dialogs.dialog>
    </div>
    <div class="preview-dialog-layer" data-dialog-layer="demo-date-picker" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog">×</button>
        <x-dialogs.dialog title="Selecionar data" description="Escolha a data para vencimento." :show-actions="false">
            <x-dialogs.date-picker name="due_date" label="Data do vencimento" value="2025-01-05" />
        </x-dialogs.dialog>
    </div>
    @foreach ($invoiceRows as $invoice)
        <div class="preview-dialog-layer" data-dialog-layer="{{ $invoice['detail_target'] }}" role="presentation" hidden>
            <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog"></button>
            <x-dialogs.details-dialog
                title="Detalhes da nota fiscal {{ $invoice['number'] }}"
                description="Informações principais do registro."
                :details="[
                    ['label' => 'Cliente', 'value' => $invoice['customer']],
                    ['label' => 'Emissão', 'value' => $invoice['issue_date']],
                    ['label' => 'Vencimento', 'value' => $invoice['due_date']],
                    ['label' => 'Prazo', 'value' => $invoice['term']],
                    ['label' => 'Valor total', 'value' => $invoice['total']],
                    ['label' => 'Status', 'value' => $invoice['status']],
                ]" />
        </div>
    @endforeach
    <div class="preview-dialog-layer" data-dialog-layer="demo-indicator-details" role="presentation" hidden>
        <button class="preview-dialog-dismiss" type="button" data-dialog-dismiss aria-label="Fechar dialog"></button>
        <x-dialogs.details-dialog
            variant="indicator"
            title="Detalhes do indicador: Total customers"
            description="Registros que compõem o valor exibido no indicador."
            :columns="[
                ['key' => 'customer', 'label' => 'Cliente'],
                ['key' => 'invoice', 'label' => 'Nota fiscal'],
                ['key' => 'total', 'label' => 'Valor'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ]"
            :rows="[
                ['customer' => 'Indústria Penha', 'invoice' => 'NF-4521', 'total' => 'R$ 8.900,00', 'status' => 'A Vencer'],
                ['customer' => 'Cristália', 'invoice' => 'NF-4522', 'total' => 'R$ 4.250,00', 'status' => 'Vencido'],
                ['customer' => 'Jampac', 'invoice' => 'NF-4523', 'total' => 'R$ 2.450,00', 'status' => 'Pago'],
            ]"
            :status-variants="['A Vencer' => 'warning', 'Vencido' => 'danger', 'Pago' => 'success']" />
    </div>
</body>

</html>
