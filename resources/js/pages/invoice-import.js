import { showFeedback } from '../components/feedback.js';

export function initializeInvoiceImport(page) {
    const layer = page.querySelector('[data-invoice-import-layer]');
    const form = layer?.querySelector('[data-invoice-import-form]');
    const openButton = page.querySelector('[data-invoice-import-open]');
    if (!form || !openButton) return;

    const file = form.elements.namedItem('xml_file');
    const review = form.querySelector('[data-import-review]');
    const previewButton = form.querySelector('[data-import-preview]');
    const refreshButton = form.querySelector('[data-import-refresh]');
    const confirmButton = form.querySelector('[data-import-confirm]');
    const loading = form.querySelector('[data-import-loading]');
    const body = form.querySelector('[data-import-items]');
    const summary = form.querySelector('[data-import-stock-summary]');
    const customer = form.elements.namedItem('customer_id');
    const paymentDate = form.elements.namedItem('payment_date');
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
        previewButton.disabled = value;
        refreshButton.disabled = value;
        layer.querySelectorAll('[data-invoice-import-close]').forEach((button) => { button.disabled = value; });
        updateStock();
    }

    function resetReview() {
        document = null;
        selected.clear();
        body.replaceChildren();
        review.hidden = true;
        review.disabled = true;
        confirmButton.hidden = true;
        refreshButton.hidden = true;
        previewButton.hidden = false;
        summary.textContent = '';
    }

    function syncPaymentDate() {
        const paid = form.elements.namedItem('payment_status').value === 'Paga';
        paymentDate.closest('label').hidden = !paid;
        paymentDate.disabled = !paid;
        paymentDate.required = paid;
        if (!paid) paymentDate.value = '';
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
            row.querySelector('[data-import-balance]').textContent = !product || !warehouseId ? 'Selecione a origem e o produto' : `${balance} un.${missing ? ' · insuficiente' : ''}`;
            row.dataset.insufficient = String(missing);
            insufficient ||= missing;
        });
        const missingProduct = document.items.some((item) => !selected.get(item.line_number));
        const ready = !missingProduct && Boolean(warehouseId) && Boolean(customer.value) && !insufficient;
        summary.textContent = missingProduct ? 'Associe todos os itens a produtos do JEMP.'
            : !customer.value ? 'Cadastre ou selecione o cliente com o CPF/CNPJ destinatário do XML.'
                : !warehouseId ? 'Escolha o armazém de origem para conferir os saldos.'
                    : insufficient ? 'Saldo insuficiente. Registre as entradas reais e atualize os cadastros. Nenhuma baixa será feita enquanto houver falta.'
                        : 'Saldos disponíveis para os itens selecionados. Confira os dados de pagamento antes de confirmar.';
        summary.dataset.error = String(insufficient);
        confirmButton.disabled = busy || !ready;
    }

    function renderItems() {
        body.replaceChildren();
        document.items.forEach((item) => {
            const row = window.document.createElement('tr');
            row.dataset.lineNumber = String(item.line_number);
            [String(item.line_number) + ' · ' + item.external_code, item.description].forEach((text) => {
                const cell = row.insertCell(); cell.textContent = text;
            });
            const select = window.document.createElement('select');
            select.setAttribute('aria-label', 'Produto para o item ' + item.line_number + ', código ' + item.external_code);
            select.dataset.externalCode = item.external_code;
            select.required = true;
            select.add(new Option('Selecione um produto', ''));
            document.products.forEach((product) => select.add(new Option(product.sku + ' · ' + product.name, String(product.id))));
            const wanted = selected.get(item.line_number) || String(item.product_id || '');
            select.value = wanted;
            selected.set(item.line_number, select.value);
            select.addEventListener('change', () => {
                document.items.filter((other) => other.external_code === item.external_code).forEach((other) => {
                    selected.set(other.line_number, select.value);
                    body.querySelectorAll('tr').forEach((candidate) => {
                        if (Number(candidate.dataset.lineNumber) === other.line_number) candidate.querySelector('select').value = select.value;
                    });
                });
                updateStock();
            });
            row.insertCell().append(select);
            [String(item.quantity), currency(item.unit_price), currency(item.subtotal)].forEach((text) => { row.insertCell().textContent = text; });
            row.insertCell().dataset.importBalance = '';
            body.append(row);
        });
    }

    async function readResponse(response) {
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Não foi possível processar o XML.');
        }
        return result;
    }

    async function preview() {
        if (busy || !file.files[0]) { if (!busy) file.reportValidity(); return; }
        const keepFinancial = document !== null;
        const previousCustomer = customer.value;
        setBusy(true);
        try {
            const payload = new FormData();
            payload.append('xml_file', file.files[0]);
            document = await readResponse(await fetch(form.dataset.previewUrl, { method: 'POST', headers: headers(), body: payload }));
            const invoice = document.invoice;
            const values = {
                number: invoice.number + ' / ' + invoice.series, issue_date: invoice.issue_date.split('-').reverse().join('/'),
                issuer: invoice.issuer_name + ' · ' + invoice.issuer_document,
                recipient: invoice.recipient_name + ' · ' + invoice.recipient_document, amount: currency(invoice.amount),
            };
            form.querySelectorAll('[data-import-value]').forEach((element) => { element.textContent = values[element.dataset.importValue] || ''; });
            customer.replaceChildren(new Option('Selecione o cliente destinatário', ''));
            document.customers.filter((candidate) => digits(candidate.document) === invoice.recipient_document)
                .forEach((candidate) => customer.add(new Option(candidate.name + ' · ' + candidate.document, String(candidate.id))));
            customer.value = keepFinancial ? previousCustomer || String(document.customer_id || '') : String(document.customer_id || '');
            if (!keepFinancial) {
                form.elements.namedItem('due_date').value = invoice.due_date || '';
                form.elements.namedItem('due_date').min = invoice.issue_date;
                form.elements.namedItem('payment_method').value = invoice.payment_method || '';
                form.elements.namedItem('payment_status').value = 'A vencer';
                const days = invoice.due_date ? (Date.parse(invoice.due_date) - Date.parse(invoice.issue_date)) / 86400000 : 0;
                form.elements.namedItem('payment_terms_days').value = String(Math.max(0, Math.round(days) || 0));
            }
            review.disabled = false;
            review.hidden = false;
            previewButton.hidden = true;
            refreshButton.hidden = false;
            confirmButton.hidden = false;
            renderItems();
            syncPaymentDate();
        } catch (error) {
            showFeedback(error.message, 'error');
        } finally {
            setBusy(false);
        }
    }

    async function confirmImport() {
        if (busy || !document || !form.reportValidity()) return;
        const payload = new FormData(form);
        document.items.forEach((item, index) => {
            payload.append(`items[${index}][line_number]`, String(item.line_number));
            payload.append(`items[${index}][product_id]`, selected.get(item.line_number) || '');
        });
        setBusy(true, 'Importando a nota e registrando a baixa…');
        try {
            const result = await readResponse(await fetch(form.dataset.importUrl, { method: 'POST', headers: headers(), body: payload }));
            sessionStorage.setItem('invoice-page-feedback', result.message);
            window.location.reload();
        } catch (error) {
            showFeedback(error.message, 'error');
            setBusy(false);
        }
    }

    openButton.addEventListener('click', () => {
        form.reset(); resetReview(); syncPaymentDate(); layer.hidden = false;
        window.document.body.classList.add('invoice-dialog-open'); file.focus();
    });
    layer.querySelectorAll('[data-invoice-import-close]').forEach((button) => button.addEventListener('click', () => {
        if (busy) return;
        layer.hidden = true; form.reset(); resetReview(); syncPaymentDate();
        window.document.body.classList.remove('invoice-dialog-open'); openButton.focus();
    }));
    file.addEventListener('change', resetReview);
    previewButton.addEventListener('click', preview);
    refreshButton.addEventListener('click', preview);
    confirmButton.addEventListener('click', confirmImport);
    form.addEventListener('submit', (event) => { event.preventDefault(); if (document) confirmImport(); else preview(); });
    ['warehouse_id', 'product_condition', 'customer_id'].forEach((name) => form.elements.namedItem(name).addEventListener('change', updateStock));
    form.elements.namedItem('payment_status').addEventListener('change', syncPaymentDate);
    form.elements.namedItem('due_date').addEventListener('change', () => {
        if (!document || !form.elements.namedItem('due_date').value) return;
        const days = (Date.parse(form.elements.namedItem('due_date').value) - Date.parse(document.invoice.issue_date)) / 86400000;
        form.elements.namedItem('payment_terms_days').value = String(Math.max(0, Math.round(days)));
    });
}
