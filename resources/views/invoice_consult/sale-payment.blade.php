<fieldset class="invoice-import-payment">
    <legend>Pagamento</legend>
    <div class="invoice-import-fields invoice-import-payment-terms">
        <label><span>Prazo de pagamento (dias) <span class="invoice-import-required" aria-hidden="true">*</span></span><input type="number" name="payment_terms_days" min="0" max="2147483647" value="0" required></label>
        <label><span>Vencimento</span><input class="invoice-import-readonly" type="date" name="due_date" readonly required></label>
    </div>
    <div class="invoice-import-fields">
        <label><span>Situação do pagamento <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="payment_status" required><option value="A vencer">Não paga</option><option value="Paga">Paga</option></select></label>
        <label hidden><span>Forma de pagamento <span class="invoice-import-required" aria-hidden="true">*</span></span><select name="payment_method" disabled><option value="">Selecione</option>@foreach (\App\Models\Payment::METHODS as $method)<option value="{{ $method }}">{{ $method }}</option>@endforeach</select></label>
        <label data-import-payment-date hidden><span>Data do pagamento <span class="invoice-import-required" aria-hidden="true">*</span></span><input type="date" name="payment_date" max="{{ \App\Models\Payment::today() }}" disabled></label>
    </div>
    <div class="invoice-import-fields invoice-import-payment-terms">
        <label><span>Quantidade de parcelas <span class="invoice-import-required" aria-hidden="true">*</span></span><input type="number" name="installment_count" min="1" max="65535" step="1" value="1" required></label>
        <div class="invoice-installment-summary"><span>Valor das parcelas</span><output data-installments aria-live="polite"></output></div>
    </div>
</fieldset>
