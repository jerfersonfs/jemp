@php
    $invoiceRows = $invoices->all();
    $columns = [
        ['key'=>'number','label'=>'Nº da nota'],
        ['key'=>'series','label'=>'Série'],
        ['key'=>'issuer','label'=>'Cliente'],
        ['key'=>'issue_date','label'=>'Emissão','class'=>'invoice-issued-date'],
        ['key'=>'due_date','label'=>'Vencimento'],
        ['key'=>'payment_date','label'=>'Pagamento'],
        ['key'=>'total','label'=>'Valor'],
        ['key'=>'status','label'=>'Situação','type'=>'status'],
        ['key'=>'details_target','label'=>'Ações','type'=>'detail','targetKey'=>'details_target','detailLabel'=>'Ver detalhes'],
    ];
    $statusVariants = ['Pendente'=>'info','A vencer'=>'warning','Vencida'=>'danger','Paga'=>'success'];
    $statusOptions = ['A vencer'=>'A vencer','Vencida'=>'Vencida','Paga'=>'Paga','Cancelada'=>'Cancelada'];
    $customerOptions = $customers->mapWithKeys(fn ($customer) => [
        $customer->customer_id => [
            'label' => $customer->customer_name.' · '.$customer->customer_document,
            'document' => $customer->customer_document,
        ],
    ])->all();
    $totalInvoices = count($invoiceRows);
    $dueSoonCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'A vencer'));
    $overdueCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'Vencida'));
    $paidCount = count(array_filter($invoiceRows, fn ($invoice) => $invoice['status'] === 'Paga'));
    $createFields = [
        ['name'=>'invoice_number','id'=>'invoice-create-number','label'=>'Número da nota','type'=>'number','required'=>true,'min'=>1],
        ['name'=>'invoice_serie','id'=>'invoice-create-series','label'=>'Série','type'=>'number','required'=>true,'min'=>1,'value'=>1],
        ['name'=>'customer_id','id'=>'invoice-create-customer','label'=>'Cliente','type'=>'select','options'=>[''=>'Selecione um cliente'] + $customerOptions,'required'=>true],
        ['name'=>'customer_document_preview','id'=>'invoice-create-document','label'=>'CPF / CNPJ','type'=>'text','placeholder'=>'Preenchido pelo cliente selecionado','readonly'=>true],
        ['name'=>'issue_date_preview','id'=>'invoice-create-issue-date','label'=>'Data de emissão','type'=>'text','value'=>now()->format('d/m/Y'),'disabled'=>true],
        ['name'=>'due_date','id'=>'invoice-create-due-date','label'=>'Vencimento','type'=>'date','required'=>true],
        ['name'=>'payment_method','id'=>'invoice-create-payment-method','label'=>'Forma de pagamento','type'=>'text','required'=>true],
        ['name'=>'payment_terms_days','id'=>'invoice-create-payment-terms','label'=>'Prazo de pagamento (dias)','type'=>'number','value'=>0,'required'=>true,'min'=>0],
        ['name'=>'payment_status','id'=>'invoice-create-status','label'=>'Situação','type'=>'select','value'=>'A vencer','options'=>$statusOptions,'required'=>true],
        ['name'=>'payment_date','id'=>'invoice-create-payment-date','label'=>'Data de pagamento','type'=>'date'],
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
            <div class="invoice-page-head__actions">

            <button class="button invoice-create-button" type="button" data-dialog-open="invoice-create">
                <i class="ph ph-plus" aria-hidden="true"></i>Adicionar nota fiscal
            </button>

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
                <span class="invoice-demo-tag">Notas registradas</span>
            </div>

            <form class="invoice-filters" action="{{ route('invoice-consult.index') }}" method="GET" role="search" aria-label="Filtros de notas fiscais" data-invoice-filter>
                <label class="invoice-filter invoice-filter--search">
                    <span>Buscar nota fiscal</span>
                    <span class="invoice-filter__control"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><input type="search" name="search" placeholder="Número, série, cliente, CNPJ ou produto" autocomplete="off"></span>
                </label>
                <label class="invoice-filter"><span>Emissão a partir de</span><input type="date" name="from"></label>
                <label class="invoice-filter"><span>Até</span><input type="date" name="to"></label>
                <label class="invoice-filter">
                    <span>Situação</span>
                    <select name="status"><option value="all">Todas as situações</option>@foreach ($statusOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                </label>
                <button class="invoice-filter-reset" type="reset" data-invoice-reset>Limpar</button>
            </form>

            <x-tables.table class="invoice-table" variant="rounded" caption="Notas fiscais cadastradas" :columns="$columns" :rows="$invoiceRows" :status-variants="$statusVariants" empty-message="Nenhuma nota fiscal cadastrada." />
            <p class="invoice-no-results" role="status" data-invoice-no-results hidden>Nenhuma nota fiscal encontrada com os filtros selecionados.</p>
            <p class="invoice-table-caption">Consulte os registros e os itens de cada nota fiscal.</p>
        </section>

        <div class="invoice-dialog-layer" data-invoice-import-layer role="presentation" hidden>
            <section class="jemp-dialog jemp-dialog--large invoice-import-dialog" role="dialog" aria-modal="true" aria-label="Importar nota fiscal XML">
                <header class="jemp-dialog__header"><div><h2 class="jemp-dialog__title">Importar NF-e por XML</h2><p class="jemp-dialog__description">Selecione o arquivo XML e o armazém que receberá os itens.</p></div><button class="jemp-dialog__close" type="button" data-invoice-import-close aria-label="Fechar">×</button></header>
                <form data-invoice-import-form enctype="multipart/form-data">
                    <label>Arquivo XML da NF-e<input type="file" name="xml_file" accept=".xml,application/xml,text/xml" required></label>
                    <label>Armazém de destino<select name="warehouse_id" required><option value="">Selecione um armazém</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>@endforeach</select></label>
                    <p data-invoice-import-feedback role="status" hidden></p>
                    <footer class="jemp-dialog__footer"><button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-invoice-import-close>Cancelar</button><button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">Importar XML</button></footer>
                </form>
            </section>
        </div>

        <div class="invoice-dialog-layer" data-dialog-layer="invoice-create" data-invoice-mode="create" role="presentation" hidden>
            <x-dialogs.form-dialog class="invoice-form-dialog" title="Cadastrar nota fiscal" description="Informe o número e os dados da nota. A data de emissão é preenchida pelo sistema; o total será calculado pelos itens." action="#" method="POST" size="large" submit-label="Cadastrar nota fiscal" :fields="$createFields" />
        </div>

        @foreach ($invoiceRows as $invoice)
            @php
                $editFields = [
                    ['name'=>'invoice_number','id'=>'invoice-edit-'.$invoice['id'].'-number','label'=>'Número da nota','type'=>'number','value'=>$invoice['number'],'required'=>true,'min'=>1],
                    ['name'=>'invoice_serie','id'=>'invoice-edit-'.$invoice['id'].'-series','label'=>'Série','type'=>'number','value'=>$invoice['series'],'required'=>true,'min'=>1],
                    ['name'=>'customer_id','id'=>'invoice-edit-'.$invoice['id'].'-customer','label'=>'Cliente','type'=>'select','value'=>$invoice['customer_id'],'options'=>[''=>'Selecione um cliente'] + $customerOptions,'required'=>true],
                    ['name'=>'customer_document_preview','id'=>'invoice-edit-'.$invoice['id'].'-document','label'=>'CPF / CNPJ','type'=>'text','value'=>$invoice['document'],'readonly'=>true],
                    ['name'=>'issue_date_preview','id'=>'invoice-edit-'.$invoice['id'].'-issue-date','label'=>'Data de emissão','type'=>'text','value'=>$invoice['issue_date'],'disabled'=>true],
                    ['name'=>'due_date','id'=>'invoice-edit-'.$invoice['id'].'-due-date','label'=>'Vencimento','type'=>'date','value'=>$invoice['due_date_iso'],'required'=>true],
                    ['name'=>'payment_method','id'=>'invoice-edit-'.$invoice['id'].'-payment-method','label'=>'Forma de pagamento','type'=>'text','value'=>$invoice['payment_method'],'required'=>true],
                    ['name'=>'payment_terms_days','id'=>'invoice-edit-'.$invoice['id'].'-payment-terms','label'=>'Prazo de pagamento (dias)','type'=>'number','value'=>$invoice['payment_terms_days'],'required'=>true,'min'=>0],
                    ['name'=>'payment_status','id'=>'invoice-edit-'.$invoice['id'].'-status','label'=>'Situação','type'=>'select','value'=>$invoice['status'],'options'=>$statusOptions,'required'=>true],
                    ['name'=>'payment_date','id'=>'invoice-edit-'.$invoice['id'].'-payment-date','label'=>'Data de pagamento','type'=>'date','value'=>$invoice['payment_date_iso']],
                ];
                $detailSections = [
                    ['id'=>'invoice-data','label'=>'Dados da nota','details'=>[
                        ['label'=>'Número','value'=>$invoice['number']],
                        ['label'=>'Série','value'=>$invoice['series']],
                        ['label'=>'Cliente','value'=>$invoice['issuer']],
                        ['label'=>'CNPJ / CPF','value'=>$invoice['document']],
                        ['label'=>'Emissão','value'=>$invoice['issue_date']],
                        ['label'=>'Vencimento','value'=>$invoice['due_date']],
                        ['label'=>'Pagamento','value'=>$invoice['payment_date']],
                        ['label'=>'Valor total calculado pelos itens','value'=>$invoice['total']],
                        ['label'=>'Situação','value'=>$invoice['status']],
                    ]],
                    ['id'=>'invoice-items','label'=>'Itens da nota','items'=>$invoice['items']],
                ];
            @endphp

            <div class="invoice-dialog-layer" data-dialog-layer="{{ $invoice['details_target'] }}" role="presentation" hidden>
                <x-dialogs.details-dialog id="{{ $invoice['details_target'] }}-tabs" title="Nota fiscal {{ $invoice['number'] }}" description="Consulte os dados e os itens desta nota fiscal." :sections="$detailSections">
                    <x-slot:footer>
                        <button class="jemp-dialog__button jemp-dialog__button--primary" type="button" data-dialog-open="{{ $invoice['add_item_target'] }}">Adicionar item</button>
                        <button class="jemp-dialog__button jemp-dialog__button--secondary invoice-edit-data-button" type="button" data-dialog-open="{{ $invoice['edit_target'] }}">Editar dados</button>
                    </x-slot:footer>
                </x-dialogs.details-dialog>
            </div>
            <div class="invoice-dialog-layer" data-dialog-layer="{{ $invoice['add_item_target'] }}" data-invoice-mode="item" data-invoice-id="{{ $invoice['id'] }}" role="presentation" hidden>
                <section class="jemp-dialog invoice-item-dialog" role="dialog" aria-modal="true" aria-label="Adicionar item à nota fiscal {{ $invoice['number'] }}">
                    <header class="jemp-dialog__header">
                        <div><h2 class="jemp-dialog__title" data-item-dialog-title>Adicionar item à nota</h2><p class="jemp-dialog__description">Escolha o produto e informe a quantidade. O preço é preenchido pelo cadastro do produto.</p></div>
                        <button class="jemp-dialog__close" type="button" data-dialog-dismiss aria-label="Fechar">×</button>
                    </header>
                    <form data-invoice-item-form>
                        <input type="hidden" name="invoice_id" value="{{ $invoice['id'] }}">
                        <label>Produto
                            <select name="product_id" required>
                                <option value="">Selecione um produto</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->product_id }}" data-unit-price="{{ $product->cost_price }}">{{ $product->product_name }} · {{ $product->sku }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Quantidade
                            <input type="number" name="quantity" min="1" step="1" required>
                        </label>
                        <label>Valor unitário (R$)
                            <input type="number" name="unit_price" min="0" step="0.01" readonly required aria-describedby="invoice-item-price-help">
                            <small id="invoice-item-price-help">Valor definido pelo cadastro do produto.</small>
                        </label>
                        <footer class="jemp-dialog__footer">
                            <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-dialog-dismiss>Cancelar</button>
                            <button class="jemp-dialog__button jemp-dialog__button--primary" type="submit" data-item-submit-label>Cadastrar item</button>
                        </footer>
                    </form>
                </section>
            </div>
            <div class="invoice-dialog-layer" data-dialog-layer="{{ $invoice['edit_target'] }}" data-invoice-mode="edit" data-invoice-id="{{ $invoice['id'] }}" role="presentation" hidden>
                    <x-dialogs.form-dialog class="invoice-form-dialog" mode="edit" title="Editar nota fiscal {{ $invoice['number'] }}" description="Atualize os dados da nota. O total permanece calculado pelos itens." action="#" method="PUT" size="large" submit-label="Salvar alterações" :fields="$editFields" />
            </div>
        @endforeach
        <div class="invoice-dialog-layer" data-dialog-layer="invoice-item-delete-confirm" data-invoice-item-confirm-layer role="presentation" hidden>
            <x-dialogs.confirmation title="Excluir item da nota?" message="" confirm-label="Excluir item" cancel-label="Cancelar" data-invoice-item-confirm-dialog />
        </div>
    </div>
</x-layouts.app>
