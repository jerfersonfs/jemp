<div class="invoice-dialog-layer" data-invoice-import-layer role="presentation" hidden>
    <section class="jemp-dialog jemp-dialog--large invoice-import-dialog" role="dialog" aria-modal="true" aria-labelledby="invoice-import-title">
        <header class="jemp-dialog__header">
            <div><h2 class="jemp-dialog__title" id="invoice-import-title">Importar NF-e de venda</h2><p class="jemp-dialog__description">Revise os produtos antes de confirmar a baixa automática do estoque.</p></div>
            <button class="jemp-dialog__close" type="button" data-invoice-import-close aria-label="Fechar">×</button>
        </header>
        <form data-invoice-import-form data-preview-url="{{ route('invoice-import.preview') }}" data-import-url="{{ route('invoice-import.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Arquivo XML da NF-e<input type="file" name="xml_file" accept=".xml,application/xml,text/xml" required></label>
            <p class="invoice-import-help">A leitura confere o formato do arquivo. Ela não consulta a SEFAZ nem valida a assinatura digital.</p>
            <p data-import-loading role="status" hidden>Lendo XML…</p>
            <fieldset data-import-review disabled hidden>
                <legend>Revisão da venda</legend>
                <dl class="invoice-import-summary">
                    <div><dt>Nota / série</dt><dd data-import-value="number"></dd></div>
                    <div><dt>Emissão</dt><dd data-import-value="issue_date"></dd></div>
                    <div><dt>Emitente</dt><dd data-import-value="issuer"></dd></div>
                    <div><dt>Destinatário</dt><dd data-import-value="recipient"></dd></div>
                    <div><dt>Total do XML</dt><dd data-import-value="amount"></dd></div>
                </dl>
                <div class="invoice-import-fields">
                    <label>Cliente destinatário<select name="customer_id" required><option value="">Selecione o cliente</option></select></label>
                    <label>Armazém de origem<select name="warehouse_id" required><option value="">Selecione um armazém</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>@endforeach</select></label>
                    <label>Condição de todos os itens<select name="product_condition" required><option value="Novo">Novo</option><option value="Reforma">Reforma</option><option value="Descarte">Descarte</option></select></label>
                    <label>Vencimento<input type="date" name="due_date" required></label>
                    <label>Forma de pagamento<input type="text" name="payment_method" maxlength="50" required></label>
                    <label>Prazo de pagamento (dias)<input type="number" name="payment_terms_days" min="0" max="2147483647" value="0" required></label>
                    <label>Pagamento<select name="payment_status" required><option value="A vencer">Não paga</option><option value="Paga">Paga</option></select></label>
                    <label data-import-payment-date hidden>Data do pagamento<input type="date" name="payment_date" disabled></label>
                </div>
                <div class="invoice-import-links">
                    <a href="{{ route('products.index') }}" target="_blank" rel="noopener">Abrir cadastro de produtos ↗</a>
                    <a href="{{ route('customers.index') }}" target="_blank" rel="noopener">Abrir cadastro de clientes ↗</a>
                    <a href="{{ route('entrance.index') }}" target="_blank" rel="noopener">Registrar entrada de estoque ↗</a>
                </div>
                <p class="invoice-import-help">Cadastros novos são independentes da nota. Depois de cadastrá-los ou registrar entradas, use “Atualizar cadastros”.</p>
                <div class="invoice-items-table-wrap" role="region" aria-label="Associação dos produtos do XML" tabindex="0">
                    <table class="invoice-items-table invoice-import-items">
                        <thead><tr><th>Item / código XML</th><th>Descrição no XML</th><th>Produto no JEMP</th><th>Qtd.</th><th>Preço unit.</th><th>Subtotal</th><th>Saldo na origem</th></tr></thead>
                        <tbody data-import-items></tbody>
                    </table>
                </div>
                <p class="invoice-import-help">As associações confirmadas serão lembradas por CNPJ do emitente e código do XML. Uma escolha diferente atualizará a associação para as próximas notas.</p>
                <p data-import-stock-summary aria-live="polite"></p>
            </fieldset>
            <footer class="jemp-dialog__footer">
                <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-invoice-import-close>Cancelar</button>
                <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-import-refresh hidden>Atualizar cadastros</button>
                <button class="jemp-dialog__button jemp-dialog__button--primary" type="button" data-import-preview>Ler XML</button>
                <button class="jemp-dialog__button jemp-dialog__button--primary" type="button" data-import-confirm hidden>Importar e baixar estoque</button>
            </footer>
        </form>
    </section>
</div>
