<div class="invoice-dialog-layer" data-invoice-import-layer role="presentation" hidden>
    <section class="jemp-dialog jemp-dialog--large invoice-import-dialog" role="dialog" aria-modal="true" aria-labelledby="invoice-import-title">
        <header class="jemp-dialog__header">
            <div><h2 class="jemp-dialog__title" id="invoice-import-title">Importar NF-e de venda</h2><p class="jemp-dialog__description">Revise os produtos antes de confirmar a baixa automática do estoque.</p></div>
            <button class="jemp-dialog__close" type="button" data-invoice-import-close aria-label="Fechar">×</button>
        </header>
        <form data-invoice-import-form data-preview-url="{{ route('invoice-import.preview') }}" data-import-url="{{ route('invoice-import.store') }}" enctype="multipart/form-data">
            @csrf
            <label class="invoice-xml-dropzone" data-import-dropzone>
                <span class="invoice-xml-dropzone__prompt">Arraste e solte seu arquivo XML aqui</span>
                <span class="invoice-xml-dropzone__hint">ou selecione um arquivo do seu dispositivo</span>
                <span class="invoice-xml-dropzone__button" aria-hidden="true">Anexar XML</span>
                <span class="invoice-xml-dropzone__filename" data-import-filename aria-live="polite"></span>
                <input class="invoice-xml-dropzone__input" type="file" name="xml_file" accept=".xml,application/xml,text/xml" aria-label="Anexar arquivo XML da NF-e" required>
            </label>
            <p class="invoice-import-help">A leitura confere o formato do arquivo. Ela não consulta a SEFAZ nem valida a assinatura digital.</p>
            <p data-import-loading role="status" hidden>Lendo XML…</p>
            <fieldset data-import-review disabled hidden>
                <legend>Revisão da venda</legend>
                <dl class="invoice-import-summary">
                    <div><dt>Número da nota</dt><dd class="invoice-import-readonly" data-import-value="number"></dd></div>
                    <div><dt>Série</dt><dd class="invoice-import-readonly" data-import-value="series"></dd></div>
                    <div><dt>Emissão</dt><dd class="invoice-import-readonly" data-import-value="issue_date"></dd></div>
                    <div><dt>Emitente</dt><dd class="invoice-import-readonly" data-import-value="issuer_name"></dd></div>
                    <div><dt>CNPJ do emitente</dt><dd class="invoice-import-readonly" data-import-value="issuer_document"></dd></div>
                    <div><dt>Destinatário</dt><dd class="invoice-import-readonly" data-import-value="recipient_name"></dd></div>
                    <div><dt>CPF/CNPJ do destinatário</dt><dd class="invoice-import-readonly" data-import-value="recipient_document"></dd></div>
                    <div><dt>Total do XML</dt><dd class="invoice-import-readonly" data-import-value="amount"></dd></div>
                </dl>
                <div class="invoice-import-fields">
                    <label><span>Cliente destinatário <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="customer_id" required><option value="">Selecione o cliente</option></select></label>
                    <label><span>Armazém de origem <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="warehouse_id" required><option value="">Selecione um armazém</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>@endforeach</select></label>
                    <label><span>Condição de todos os itens <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="product_condition" required><option value="Novo">Novo</option><option value="Reforma">Reforma</option><option value="Descarte">Descarte</option></select></label>
                </div>
                <fieldset class="invoice-import-payment">
                    <legend>Pagamento</legend>
                    <div class="invoice-import-fields invoice-import-payment-terms">
                        <label><span>Prazo de pagamento (dias) <span class="invoice-import-required" aria-hidden="true">*</span></span><input type="number" name="payment_terms_days" min="0" max="2147483647" value="0" required></label>
                        <label><span>Vencimento</span><input class="invoice-import-readonly" type="date" name="due_date" readonly required></label>
                    </div>
                    <div class="invoice-import-fields">
                        <label><span>Situação do pagamento <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="payment_status" required><option value="A vencer">Não paga</option><option value="Paga">Paga</option></select></label>
                        <label hidden><span>Forma de pagamento <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="payment_method" disabled><option value="">Selecione</option>@foreach (\App\Models\Invoice::PAYMENT_METHODS as $method)<option value="{{ $method }}">{{ $method }}</option>@endforeach</select></label>
                        <label data-import-payment-date hidden><span>Data do pagamento <span class="invoice-import-required" aria-hidden="true">*</span></span><input type="date" name="payment_date" disabled></label>
                    </div>
                </fieldset>
                <div class="invoice-items-table-wrap" role="region" aria-label="Conferência dos produtos do XML" tabindex="0">
                    <table class="invoice-items-table invoice-import-items">
                        <thead><tr><th>Item / código XML</th><th>Descrição no XML</th><th>Conferência no JEMP</th><th>Qtd.</th><th>Preço unit.</th><th>Subtotal</th><th>Saldo na origem</th></tr></thead>
                        <tbody data-import-items></tbody>
                    </table>
                </div>
                <p class="invoice-import-help">O código do XML deve ser igual ao código do produto no JEMP. Produtos não encontrados bloqueiam a importação; descrições diferentes geram somente um aviso.</p>
                <p class="invoice-import-notice" data-import-stock-summary role="status" aria-live="polite"></p>
            </fieldset>
            <p class="invoice-import-notice" data-import-error data-tone="error" role="alert" hidden></p>
            <footer class="jemp-dialog__footer">
                <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-invoice-import-close>Cancelar</button>
                <button class="jemp-dialog__button jemp-dialog__button--primary" type="button" data-import-confirm hidden>Importar e baixar estoque</button>
            </footer>
        </form>
    </section>
</div>
