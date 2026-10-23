@php
    $invoiceRows = [
        ['id'=>'nfe-278','number'=>'000278','series'=>'1','issuer'=>'Comercial Aurora Ltda.','document'=>'12.345.678/0001-90','issue_date'=>'27/09/2026','issue_date_iso'=>'2026-09-27','due_date'=>'15/10/2026','due_date_iso'=>'2026-10-15','payment_date'=>'—','payment_date_iso'=>'','amount'=>'1500.00','total'=>'R$ 1.500,00','status'=>'Vencida','details_target'=>'invoice-details-nfe-278','edit_target'=>'invoice-edit-nfe-278','search_terms'=>'000278 1 Comercial Aurora Ltda 12.345.678/0001-90 material de escritório papel sulfite','items'=>[['label'=>'Papel sulfite A4','value'=>'10 caixas · R$ 1.200,00'],['label'=>'Material de escritório','value'=>'1 conjunto · R$ 300,00']]],
        ['id'=>'nfe-277','number'=>'000277','series'=>'1','issuer'=>'Distribuidora Central','document'=>'23.456.789/0001-01','issue_date'=>'25/09/2026','issue_date_iso'=>'2026-09-25','due_date'=>'27/10/2026','due_date_iso'=>'2026-10-27','payment_date'=>'—','payment_date_iso'=>'','amount'=>'840.00','total'=>'R$ 840,00','status'=>'A vencer','details_target'=>'invoice-details-nfe-277','edit_target'=>'invoice-edit-nfe-277','search_terms'=>'000277 1 Distribuidora Central 23.456.789/0001-01 embalagens caixas','items'=>[['label'=>'Caixas para expedição','value'=>'40 unidades · R$ 840,00']]],
        ['id'=>'nfe-276','number'=>'000276','series'=>'3','issuer'=>'Mercado do Vale','document'=>'34.567.890/0001-12','issue_date'=>'18/09/2026','issue_date_iso'=>'2026-09-18','due_date'=>'18/10/2026','due_date_iso'=>'2026-10-18','payment_date'=>'17/10/2026','payment_date_iso'=>'2026-10-17','amount'=>'2340.50','total'=>'R$ 2.340,50','status'=>'Paga','details_target'=>'invoice-details-nfe-276','edit_target'=>'invoice-edit-nfe-276','search_terms'=>'000276 3 Mercado do Vale 34.567.890/0001-12 alimentos arroz feijão','items'=>[['label'=>'Arroz tipo 1','value'=>'20 unidades · R$ 1.240,50'],['label'=>'Feijão carioca','value'=>'20 unidades · R$ 1.100,00']]],
        ['id'=>'nfe-275','number'=>'000275','series'=>'1','issuer'=>'Oficina Horizonte','document'=>'45.678.901/0001-23','issue_date'=>'10/09/2026','issue_date_iso'=>'2026-09-10','due_date'=>'10/10/2026','due_date_iso'=>'2026-10-10','payment_date'=>'09/10/2026','payment_date_iso'=>'2026-10-09','amount'=>'685.90','total'=>'R$ 685,90','status'=>'Paga','details_target'=>'invoice-details-nfe-275','edit_target'=>'invoice-edit-nfe-275','search_terms'=>'000275 1 Oficina Horizonte 45.678.901/0001-23 manutenção ferramentas','items'=>[['label'=>'Ferramentas e peças','value'=>'1 conjunto · R$ 685,90']]],
    ];
    $columns = [
        ['key'=>'number','label'=>'Nº da nota'],
        ['key'=>'series','label'=>'Série'],
        ['key'=>'issuer','label'=>'Cliente / fornecedor'],
        ['key'=>'issue_date','label'=>'Emissão','class'=>'invoice-issued-date'],
        ['key'=>'due_date','label'=>'Vencimento'],
        ['key'=>'payment_date','label'=>'Pagamento'],
        ['key'=>'total','label'=>'Valor'],
        ['key'=>'status','label'=>'Situação','type'=>'status'],
        ['key'=>'details_target','label'=>'Ações','type'=>'detail','targetKey'=>'details_target','detailLabel'=>'Ver detalhes'],
    ];
    $statusVariants = ['Pendente'=>'info','A vencer'=>'warning','Vencida'=>'danger','Paga'=>'success'];
    $statusOptions = ['Pendente'=>'Pendente','A vencer'=>'A vencer','Vencida'=>'Vencida','Paga'=>'Paga'];
    $totalInvoices = count($invoiceRows);
    $dueSoonCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'A vencer'));
    $overdueCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'Vencida'));
    $paidCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'Paga'));
    $createFields = [
        ['name'=>'number','id'=>'invoice-create-number','label'=>'Número da nota','type'=>'text','placeholder'=>'Gerado pelo sistema ao salvar','readonly'=>true],
        ['name'=>'series','id'=>'invoice-create-series','label'=>'Série','type'=>'text','required'=>true],
        ['name'=>'issuer','id'=>'invoice-create-issuer','label'=>'Cliente / fornecedor','type'=>'text','required'=>true],
        ['name'=>'document','id'=>'invoice-create-document','label'=>'CNPJ / CPF','type'=>'text','required'=>true,'placeholder'=>'00.000.000/0000-00'],
        ['name'=>'issue_date','id'=>'invoice-create-issue-date','label'=>'Data de emissão','type'=>'date','value'=>now()->toDateString(),'readonly'=>true],
        ['name'=>'due_date','id'=>'invoice-create-due-date','label'=>'Vencimento','type'=>'date','required'=>true],
        ['name'=>'payment_date','id'=>'invoice-create-payment-date','label'=>'Data de pagamento','type'=>'date'],
        ['name'=>'amount','id'=>'invoice-create-amount','label'=>'Valor total (R$)','type'=>'number','required'=>true,'placeholder'=>'0,00'],
        ['name'=>'status','id'=>'invoice-create-status','label'=>'Situação','type'=>'select','value'=>'Pendente','options'=>$statusOptions,'required'=>true],
        ['name'=>'notes','id'=>'invoice-create-notes','label'=>'Observações','type'=>'textarea'],
    ];
@endphp

<x-layouts.app title="Notas fiscais · JEMP" page="invoice-consult">
    <div class="invoice-consult" data-invoice-page>
        <header class="invoice-page-head">
            <div>
                <p class="invoice-eyebrow">Financeiro</p>
                <h1>Consultar Nota Fiscal</h1>
                <p class="invoice-page-description">Gerencie e consulte todas as suas notas fiscais.</p>
            </div>
            <button class="button invoice-create-button" type="button" data-dialog-open="invoice-create">
                <i class="ph ph-plus" aria-hidden="true"></i>Adicionar nota fiscal
            </button>
        </header>

        <section class="invoice-overview" aria-label="Resumo das notas fiscais">
            <article class="invoice-stat card"><div class="invoice-stat__label"><i class="ph ph-files" aria-hidden="true"></i>Notas no período</div><strong data-invoice-count="all">{{ $totalInvoices }}</strong><span>registros demonstrativos</span></article>
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
                <span class="invoice-demo-tag">Dados de demonstração</span>
            </div>

            <form class="invoice-filters" action="{{ route('invoice-consult.index') }}" method="GET" role="search" aria-label="Filtros de notas fiscais" data-invoice-filter>
                <label class="invoice-filter invoice-filter--search">
                    <span>Buscar nota fiscal</span>
                    <span class="invoice-filter__control"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><input type="search" name="search" placeholder="Número, série, cliente, CNPJ ou produto" autocomplete="off"></span>
                </label>
                <label class="invoice-filter"><span>Emissão a partir de</span><input type="date" name="from" value="2026-09-01"></label>
                <label class="invoice-filter"><span>Até</span><input type="date" name="to" value="2026-09-30"></label>
                <label class="invoice-filter">
                    <span>Situação</span>
                    <select name="status"><option value="all">Todas as situações</option>@foreach ($statusOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                </label>
                <button class="invoice-filter-reset" type="reset" data-invoice-reset>Limpar</button>
            </form>

            <x-tables.table class="invoice-table" variant="rounded" caption="Notas fiscais cadastradas" :columns="$columns" :rows="$invoiceRows" :status-variants="$statusVariants" empty-message="Nenhuma nota fiscal cadastrada." />
            <p class="invoice-no-results" role="status" data-invoice-no-results hidden>Nenhuma nota fiscal encontrada com os filtros selecionados.</p>
            <p class="invoice-table-caption">Os dados exibidos são fictícios e servem apenas para demonstrar a interface.</p>
        </section>

        <div class="invoice-feedback" data-invoice-feedback hidden>
            <x-dialogs.alert variant="info" title="Interface demonstrativa" message="Os campos foram validados. A gravação será conectada ao backend." :dismissible="false" />
        </div>

        <div class="invoice-dialog-layer" data-dialog-layer="invoice-create" data-invoice-mode="create" role="presentation" hidden>
            <x-dialogs.form-dialog class="invoice-form-dialog" title="Cadastrar nota fiscal" description="Preencha os dados da nota. O número e a data de emissão são definidos pelo sistema." action="#" method="POST" size="large" submit-label="Cadastrar nota fiscal" :fields="$createFields" />
        </div>

        @foreach ($invoiceRows as $invoice)
            @php
                $editFields = [
                    ['name'=>'number','id'=>'invoice-edit-'.$invoice['id'].'-number','label'=>'Número da nota','type'=>'text','value'=>$invoice['number'],'readonly'=>true],
                    ['name'=>'series','id'=>'invoice-edit-'.$invoice['id'].'-series','label'=>'Série','type'=>'text','value'=>$invoice['series'],'required'=>true],
                    ['name'=>'issuer','id'=>'invoice-edit-'.$invoice['id'].'-issuer','label'=>'Cliente / fornecedor','type'=>'text','value'=>$invoice['issuer'],'required'=>true],
                    ['name'=>'document','id'=>'invoice-edit-'.$invoice['id'].'-document','label'=>'CNPJ / CPF','type'=>'text','value'=>$invoice['document'],'required'=>true],
                    ['name'=>'issue_date','id'=>'invoice-edit-'.$invoice['id'].'-issue-date','label'=>'Data de emissão','type'=>'date','value'=>$invoice['issue_date_iso'],'readonly'=>true],
                    ['name'=>'due_date','id'=>'invoice-edit-'.$invoice['id'].'-due-date','label'=>'Vencimento','type'=>'date','value'=>$invoice['due_date_iso'],'required'=>true],
                    ['name'=>'payment_date','id'=>'invoice-edit-'.$invoice['id'].'-payment-date','label'=>'Data de pagamento','type'=>'date','value'=>$invoice['payment_date_iso']],
                    ['name'=>'amount','id'=>'invoice-edit-'.$invoice['id'].'-amount','label'=>'Valor total (R$)','type'=>'number','value'=>$invoice['amount'],'required'=>true],
                    ['name'=>'status','id'=>'invoice-edit-'.$invoice['id'].'-status','label'=>'Situação','type'=>'select','value'=>$invoice['status'],'options'=>$statusOptions,'required'=>true],
                    ['name'=>'notes','id'=>'invoice-edit-'.$invoice['id'].'-notes','label'=>'Observações','type'=>'textarea'],
                ];
                $detailSections = [
                    ['id'=>'invoice-data','label'=>'Dados da nota','details'=>[
                        ['label'=>'Número','value'=>$invoice['number']],
                        ['label'=>'Série','value'=>$invoice['series']],
                        ['label'=>'Cliente / fornecedor','value'=>$invoice['issuer']],
                        ['label'=>'CNPJ / CPF','value'=>$invoice['document']],
                        ['label'=>'Emissão','value'=>$invoice['issue_date']],
                        ['label'=>'Vencimento','value'=>$invoice['due_date']],
                        ['label'=>'Pagamento','value'=>$invoice['payment_date']],
                        ['label'=>'Valor total','value'=>$invoice['total']],
                        ['label'=>'Situação','value'=>$invoice['status']],
                    ]],
                    ['id'=>'invoice-items','label'=>'Itens da nota','details'=>$invoice['items']],
                ];
            @endphp

            <div class="invoice-dialog-layer" data-dialog-layer="{{ $invoice['details_target'] }}" role="presentation" hidden>
                <x-dialogs.details-dialog id="{{ $invoice['details_target'] }}-tabs" title="Nota fiscal {{ $invoice['number'] }}" description="Consulte os dados e os itens desta nota fiscal." :sections="$detailSections">
                    <x-slot:footer><button class="jemp-dialog__button jemp-dialog__button--primary" type="button" data-dialog-open="{{ $invoice['edit_target'] }}">Editar dados</button></x-slot:footer>
                </x-dialogs.details-dialog>
            </div>
            <div class="invoice-dialog-layer" data-dialog-layer="{{ $invoice['edit_target'] }}" data-invoice-mode="edit" role="presentation" hidden>
                <x-dialogs.form-dialog class="invoice-form-dialog" mode="edit" title="Editar nota fiscal {{ $invoice['number'] }}" description="Atualize as informações permitidas e salve as alterações." action="#" method="PUT" size="large" submit-label="Salvar alterações" :fields="$editFields" />
            </div>
        @endforeach
    </div>
</x-layouts.app>
