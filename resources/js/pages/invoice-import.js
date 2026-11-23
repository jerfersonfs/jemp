import { requestJson } from '../components/loading.js';
import { reloadWithFeedback } from '../components/feedback.js';

import { salePaymentFields } from './sale-payment.js';

export function initializeInvoiceImport(page) {
    const layer = page.querySelector('[data-invoice-import-layer]');
    const form = layer?.querySelector('[data-invoice-import-form]');
    const openButton = page.querySelector('[data-invoice-import-open]');
    if (!form || !openButton) return;

    const file = form.elements.namedItem('xml_file');
    const dropzone = form.querySelector('[data-import-dropzone]');
    const filename = form.querySelector('[data-import-filename]');
    const review = form.querySelector('[data-import-review]');
    const confirmButton = form.querySelector('[data-import-confirm]');
    const loading = form.querySelector('[data-import-loading]');
    const body = form.querySelector('[data-import-items]');
    const summary = form.querySelector('[data-import-stock-summary]');
    const errorNotice = form.querySelector('[data-import-error]');
    const customer = form.elements.namedItem('customer_id');
    const payment = salePaymentFields(form);
    let document = null;
    let busy = false;
    const selected = new Map();
    const currency = (value) => Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const digits = (value) => String(value || '').replace(/\D/g, '');
    const headers = () => ({ Accept: 'application/json', 'X-CSRF-TOKEN': form.elements.namedItem('_token').value });

    function setBusy(value, message = 'Lendo XML…') {
        busy = value;
        loading.textContent = message;
        loading.hidden = !value;
        form.setAttribute('aria-busy', String(value));
        file.disabled = value;
        review.disabled = value || !document;
        layer.querySelectorAll('[data-invoice-import-close]').forEach((button) => { button.disabled = value; });
        updateStock();
    }

    function resetReview() {
        document = null;
        dropzone.hidden = false;
        dropzone.classList.remove('is-dragging');
        filename.textContent = file.files[0]?.name || '';
        selected.clear();
        body.replaceChildren();
        review.hidden = true;
        review.disabled = true;
        confirmButton.hidden = true;
        summary.textContent = '';
        errorNotice.hidden = true;
        errorNotice.textContent = '';
    }

    function updateStock() {
        if (!document) { confirmButton.disabled = true; return; }
        const warehouseId = form.elements.namedItem('warehouse_id').value;
        const condition = form.elements.namedItem('product_condition').value;
        const totals = new Map();
        document.items.forEach((item) => {
            const productId = selected.get(item.line_number) || '';
            totals.set(productId, (totals.get(productId) || 0) + item.quantity);
        });
        let insufficient = false;
        body.querySelectorAll('tr').forEach((row) => {
            const productId = selected.get(Number(row.dataset.lineNumber));
            const product = document.products.find((candidate) => String(candidate.id) === String(productId));
            const stock = product?.inventory.find((item) => String(item.warehouse_id) === warehouseId && item.condition === condition);
            const balance = Number(stock?.balance || 0);
            const missing = Boolean(product && warehouseId && balance < totals.get(String(product.id)));
            row.querySelector('[data-import-balance]').textContent = !product ? 'Produto não cadastrado' : !warehouseId ? 'Selecione a origem' : `${balance} un.${missing ? ' · insuficiente' : ''}`;
            row.dataset.insufficient = String(missing);
            insufficient ||= missing;
        });
        const missingProduct = document.items.some((item) => !selected.get(item.line_number));
        const ready = Boolean(condition) && !missingProduct && Boolean(warehouseId) && Boolean(customer.value) && !insufficient;
        summary.textContent = !condition ? 'Habilite uma condição de venda na Parametrização para importar a nota.' : insufficient
            ? 'Saldo insuficiente. Registre as entradas necessárias e abra uma nova importação para conferir os saldos. A importação ficará bloqueada enquanto houver falta.'
            : missingProduct ? 'Há códigos não cadastrados no JEMP. Cadastre os produtos e reabra o XML antes de importar.'
                : !customer.value ? 'Cadastre ou selecione o cliente com o CPF/CNPJ destinatário do XML.'
                    : !warehouseId ? 'Escolha o armazém de origem para conferir os saldos.'
                        : 'Saldos disponíveis. Confira os dados de pagamento antes de confirmar.';
        summary.dataset.tone = insufficient ? 'error' : ready ? 'success' : 'warning';
        confirmButton.disabled = busy || !ready;
    }

    function renderItems() {
        body.replaceChildren();
        document.items.forEach((item) => {
            const row = window.document.createElement('tr');
            row.dataset.lineNumber = String(item.line_number);
            [String(item.line_number) + ' · ' + item.external_code, item.description].forEach((text) => {
                row.insertCell().textContent = text;
            });
            const product = document.products.find((candidate) => candidate.id === item.product_id);
            selected.set(item.line_number, product ? String(product.id) : '');
            row.dataset.productWarning = String(!product || item.description_mismatch);
            const result = row.insertCell();
            result.textContent = product ? product.sku + ' · ' + product.name : 'Código não cadastrado no JEMP';
            if (item.description_mismatch) {
                const warning = window.document.createElement('small');
                warning.textContent = 'Descrição diferente: confira. O código coincide e a importação está liberada.';
                result.append(warning);
            }
            [String(item.quantity), currency(item.unit_price), currency(item.subtotal)].forEach((text) => {
                row.insertCell().textContent = text;
            });
            row.insertCell().dataset.importBalance = '';
            body.append(row);
        });
    }

    async function submitXml(url, payload) {
        const { response, data: result } = await requestJson(url, { method: 'POST', headers: headers(), body: payload });
        if (!response.ok) {
            throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Não foi possível processar o XML.');
        }
        return result;
    }

    async function preview() {
        if (busy || !file.files[0]) { if (!busy) file.reportValidity(); return; }
        const keepFinancial = document !== null;
        const previousCustomer = customer.value;
        errorNotice.hidden = true;
        setBusy(true);
        try {
            const payload = new FormData();
            payload.append('xml_file', file.files[0]);
            document = await submitXml(form.dataset.previewUrl, payload);
            const invoice = document.invoice;
            const values = {
                number: invoice.number, series: invoice.series, issue_date: invoice.issue_date.split('-').reverse().join('/'),
                issuer_name: invoice.issuer_name, issuer_document: invoice.issuer_document,
                recipient_name: invoice.recipient_name, recipient_document: invoice.recipient_document,
                amount: currency(invoice.amount),
            };
            form.querySelectorAll('[data-import-value]').forEach((element) => { element.textContent = values[element.dataset.importValue] || ''; });
            customer.replaceChildren(new Option('Selecione o cliente destinatário', ''));
            document.customers.filter((candidate) => digits(candidate.document) === invoice.recipient_document)
                .forEach((candidate) => customer.add(new Option(candidate.name + ' · ' + candidate.document, String(candidate.id))));
            customer.value = keepFinancial ? previousCustomer || String(document.customer_id || '') : String(document.customer_id || '');
            if (!keepFinancial) payment.reset(invoice);
            review.disabled = false;
            review.hidden = false;
            file.closest('label').hidden = true;
            confirmButton.hidden = false;
            renderItems();
        } catch (error) {
            file.value = '';
            resetReview();
            errorNotice.textContent = error.message;
            errorNotice.hidden = false;
            errorNotice.scrollIntoView({ block: 'nearest' });
        } finally {
            setBusy(false);
            if (document) customer.focus();
        }
    }

    async function confirmImport() {
        if (busy || !document || !form.reportValidity()) return;
        const payload = new FormData(form);
        errorNotice.hidden = true;
        setBusy(true, 'Importando a nota e registrando a baixa…');
        try {
            const result = await submitXml(form.dataset.importUrl, payload);
            reloadWithFeedback(result.message);
        } catch (error) {
            errorNotice.textContent = error.message;
            errorNotice.hidden = false;
            errorNotice.scrollIntoView({ block: 'nearest' });
            setBusy(false);
        }
    }

    openButton.addEventListener('click', () => {
        form.reset(); resetReview(); layer.hidden = false;
        window.document.body.classList.add('invoice-dialog-open'); file.focus();
    });
    layer.querySelectorAll('[data-invoice-import-close]').forEach((button) => button.addEventListener('click', () => {
        if (busy) return;
        layer.hidden = true; form.reset(); resetReview();
        window.document.body.classList.remove('invoice-dialog-open'); openButton.focus();
    }));
    function selectFile() {
        if (busy) return;
        resetReview();
        if (file.files.length && !/\.xml$/i.test(file.files[0].name)) {
            file.value = '';
            filename.textContent = '';
            errorNotice.textContent = 'Selecione um arquivo com extensão .xml.';
            errorNotice.hidden = false;
            return;
        }
        if (file.files.length) preview();
    }

    file.addEventListener('change', selectFile);
    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = busy ? 'none' : 'copy';
        if (!busy) dropzone.classList.add('is-dragging');
    });
    dropzone.addEventListener('dragleave', (event) => {
        if (!dropzone.contains(event.relatedTarget)) dropzone.classList.remove('is-dragging');
    });
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');
        if (busy) return;
        if (event.dataTransfer.files.length !== 1) {
            errorNotice.textContent = 'Anexe um arquivo XML por vez.';
            errorNotice.hidden = false;
            return;
        }
        file.files = event.dataTransfer.files;
        selectFile();
    });
    confirmButton.addEventListener('click', confirmImport);
    form.addEventListener('submit', (event) => { event.preventDefault(); if (document) confirmImport(); else preview(); });
    ['warehouse_id', 'product_condition', 'customer_id'].forEach((name) => form.elements.namedItem(name).addEventListener('change', updateStock));
}
