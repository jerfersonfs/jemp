@php
    $invoiceRows = $invoices->all();
    $columns = [
        ['key'=>'number','label'=>'Nº da nota'],
        ['key'=>'series','label'=>'Série'],
        ['key'=>'issuer_document','label'=>'CNPJ emitente'],
        ['key'=>'issuer','label'=>'Cliente'],
        ['key'=>'issue_date','label'=>'Emissão','class'=>'invoice-issued-date'],
        ['key'=>'due_date','label'=>'Vencimento'],
        ['key'=>'payment_date','label'=>'Pagamento'],
        ['key'=>'total','label'=>'Valor'],
        ['key'=>'status','label'=>'Situação','type'=>'status'],
        ['key'=>'details_target','label'=>'Ações','type'=>'detail','targetKey'=>'details_target','detailLabel'=>'Ver detalhes'],
    ];
    $statusVariants = ['Pendente'=>'info','A vencer'=>'warning','Vencida'=>'danger','Paga'=>'success'];
    $statusOptions = ['A vencer'=>'A vencer','Vencida'=>'Vencida','Paga'=>'Paga','Cancelada'=>'Cancelada','Sem pagamento cadastrado'=>'Sem pagamento cadastrado'];
    $totalInvoices = count($invoiceRows);
    $dueSoonCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'A vencer'));
    $overdueCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'Vencida'));
    $paidCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'Paga'));
@endphp

<x-layouts.app title="Notas fiscais · JEMP" page="invoice-consult">
    <div class="invoice-consult" data-invoice-page>
        <header class="invoice-page-head">
            <div>
                <p class="invoice-eyebrow">Financeiro</p>
                <h1>Consultar Nota Fiscal</h1>
                <p class="invoice-page-description">Gerencie e consulte todas as suas notas fiscais.</p>
            </div>
            <div class="invoice-page-head__actions">
                <button class="btn btn-secondary" type="button" data-dialog-open="invoice-create">Nova nota manual</button>
                <button class="btn btn-secondary invoice-import-button" type="button" data-invoice-import-open><i class="ph ph-upload-simple" aria-hidden="true"></i>Importar XML de venda</button>
            </div>
        </header>

        <section class="invoice-overview" aria-label="Resumo das notas fiscais">
            <article class="invoice-stat card"><div class="invoice-stat__label"><i class="ph ph-files" aria-hidden="true"></i>Notas no período</div><strong data-invoice-count="all">{{ $totalInvoices }}</strong><span>notas cadastradas</span></article>
            <article class="invoice-stat card"><div class="invoice-stat__label"><i class="ph ph-clock" aria-hidden="true"></i>A vencer</div><strong data-invoice-count="due">{{ $dueSoonCount }}</strong><span>acompanhe os prazos</span></article>
            <article class="invoice-stat card"><div class="invoice-stat__label"><i class="ph ph-warning-circle" aria-hidden="true"></i>Vencidas</div><strong data-invoice-count="overdue">{{ $overdueCount }}</strong><span>precisam de atenção</span></article>
            <article class="invoice-stat card"><div class="invoice-stat__label"><i class="ph ph-check-circle" aria-hidden="true"></i>Pagas</div><strong data-invoice-count="paid">{{ $paidCount }}</strong><span>pagamentos concluídos</span></article>
        </section>

        <section class="invoice-panel card" aria-labelledby="invoice-list-title">
            <div class="invoice-panel__header">
                <div class="invoice-panel__title">
                    <span class="invoice-panel__icon" aria-hidden="true"><i class="ph ph-receipt"></i></span>
                    <div><h2 id="invoice-list-title">Lista de Notas Fiscais</h2><p>Visualize, filtre e consulte os dados das notas fiscais.</p></div>
                </div>
            </div>

            <x-filters.filter-bar class="invoice-filters" :action="route('invoice-consult.index')" label="Filtros de notas fiscais" data-invoice-filter>
                <x-inputs.input id="invoice-search" name="search" type="search" label="Buscar nota fiscal" placeholder="Número, série, cliente, CNPJ ou produto" autocomplete="off">
                    <x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading>
                </x-inputs.input>
                <x-inputs.input name="from" type="date" label="Emissão a partir de" />
                <x-inputs.input name="to" type="date" label="Até" />
                <x-inputs.input name="status" type="select" label="Situação" :options="['all' => 'Todas as situações'] + $statusOptions" />
                <x-buttons.button class="invoice-filter-reset" variant="secondary" type="reset" data-invoice-reset>Limpar</x-buttons.button>
            </x-filters.filter-bar>

            <x-tables.table class="invoice-table" variant="rounded" caption="Notas fiscais cadastradas" :columns="$columns" :rows="$invoiceRows" :status-variants="$statusVariants" empty-message="Nenhuma nota fiscal cadastrada." />
            <p class="invoice-no-results" role="status" data-invoice-no-results hidden>Nenhuma nota fiscal encontrada com os filtros selecionados.</p>
            <footer class="movement-table-footer">
                <span class="movement-table-footer__summary" data-invoice-pagination-summary aria-live="polite"></span>
                <nav class="movement-pagination" aria-label="Paginação de notas fiscais">
                    <button class="btn btn-secondary" type="button" data-invoice-previous disabled><x-icons.icon name="caret-left" size="sm" /> Anterior</button>
                    <span class="movement-pagination__current" data-invoice-current aria-live="polite">1 / 1</span>
                    <button class="btn btn-secondary" type="button" data-invoice-next disabled>Próximo <x-icons.icon name="caret-right" size="sm" /></button>
                </nav>
            </footer>
        </section>

        @include('invoice_consult.import-xml')

        @include('invoice_consult.manual')
        @foreach ($invoiceRows as $invoice)
            @php
                $detailSections = [
                    ['id'=>'invoice-data','label'=>'Dados da nota','details'=>[
                        ['label'=>'Número','value'=>$invoice['number']],
                        ['label'=>'Série','value'=>$invoice['series']],
                        ['label'=>'CNPJ emitente','value'=>$invoice['issuer_document']],
                        ['label'=>'Chave de acesso','value'=>$invoice['access_key'] ?? '—'],
                        ['label'=>'Estoque','value'=>$invoice['imported_xml'] ? 'Saída registrada' : 'Sem baixa automática por XML'],
                        ['label'=>'Cliente','value'=>$invoice['issuer']],
                        ['label'=>'CNPJ / CPF','value'=>$invoice['document']],
                        ['label'=>'Emissão','value'=>$invoice['issue_date']],
                        ['label'=>'Vencimento','value'=>$invoice['due_date']],
                        ['label'=>'Pagamento','value'=>$invoice['payment_date']],
                        ['label'=>'Valor total calculado pelos itens','value'=>$invoice['total']],
                        ['label'=>'Situação','value'=>$invoice['status']],
                    ]],
                    ['id'=>'invoice-items','label'=>'Itens da nota','items'=>$invoice['items'],'items_locked'=>true],
                ];
            @endphp

            <div class="invoice-dialog-layer" data-dialog-layer="{{ $invoice['details_target'] }}" role="presentation" hidden>
                <x-dialogs.details-dialog id="{{ $invoice['details_target'] }}-tabs" title="Nota fiscal {{ $invoice['number'] }}" description="Consulte os dados e os itens desta nota fiscal." :sections="$detailSections">
                    @include('invoice_consult.payment-detail')
                </x-dialogs.details-dialog>
            </div>
        @endforeach
    </div>
</x-layouts.app>
