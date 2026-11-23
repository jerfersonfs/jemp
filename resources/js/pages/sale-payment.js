const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
const cents = (value) => Math.round(Number(value || 0) * 100);

export function salePaymentFields(form) {
    const fields = form.elements;
    const count = fields.namedItem('installment_count');
    const terms = fields.namedItem('payment_terms_days');
    const due = fields.namedItem('due_date');
    const status = fields.namedItem('payment_status');
    const method = fields.namedItem('payment_method');
    const paidAt = fields.namedItem('payment_date');
    const summary = form.querySelector('[data-installments]');
    let total = 0;
    let issued = '';

    function showInstallments() {
        const parts = count.valueAsNumber;
        const valid = Number.isInteger(parts) && parts >= 1 && parts <= Math.max(1, total) && parts <= 65535;
        count.setCustomValidity(valid || !count.value ? '' : 'Informe uma quantidade válida de parcelas para o total da venda.');
        if (!valid) {
            summary.textContent = '—';
            return;
        }
        const base = Math.floor(total / parts);
        const extra = total % parts;
        const groups = [(parts - extra) + ' × ' + money.format(base / 100)];
        if (extra) groups.push(extra + ' × ' + money.format((base + 1) / 100));
        summary.textContent = groups.join(' + ');
    }

    function showDueDate() {
        const days = terms.valueAsNumber;
        const date = new Date(issued + 'T00:00:00Z');
        date.setUTCDate(date.getUTCDate() + days);
        const valid = Number.isInteger(days) && days >= 0 && Number.isFinite(date.getTime()) && date.getUTCFullYear() <= 9999;
        terms.setCustomValidity(valid || !issued ? '' : 'Informe um prazo que resulte em uma data válida.');
        due.value = valid ? date.toISOString().slice(0, 10) : '';
    }

    function showReceiptFields() {
        const paid = status.value === 'Paga';
        for (const field of [method, paidAt]) {
            field.closest('label').hidden = !paid;
            field.disabled = !paid;
            field.required = paid;
        }
        if (!paid) paidAt.value = '';
    }

    count.addEventListener('input', showInstallments);
    terms.addEventListener('input', showDueDate);
    status.addEventListener('change', showReceiptFields);

    return {
        reset(invoice) {
            issued = invoice.issue_date;
            total = cents(invoice.amount);
            count.value = invoice.installment_count || 1;
            const days = (Date.parse(invoice.due_date) - Date.parse(issued)) / 86400000;
            terms.value = Number.isFinite(days) ? Math.max(0, Math.round(days)) : 0;
            method.value = invoice.payment_method || '';
            status.value = 'A vencer';
            showDueDate();
            showReceiptFields();
            showInstallments();
        },
        setTotal(value) {
            total = value;
            showInstallments();
        },
        setIssueDate(value) {
            issued = value;
            showDueDate();
        },
    };
}

export function initializeManualInvoice(page) {
    const form = page.querySelector('[data-manual-invoice]');
    if (!form) return;
    const items = form.querySelector('[data-manual-items]');
    const template = form.querySelector('[data-item-template]');
    const payment = salePaymentFields(form);

    function refresh() {
        let total = 0;
        Array.from(items.children).forEach((row, index) => {
            const product = row.querySelector('[data-product]');
            const quantity = row.querySelector('[data-quantity]');
            product.name = 'items[' + index + '][product_id]';
            quantity.name = 'items[' + index + '][quantity]';
            total += cents(product.selectedOptions[0]?.dataset.price) * Number(quantity.value);
            row.querySelector('[data-remove-item]').disabled = items.children.length === 1;
        });
        payment.setTotal(total);
    }

    function addItem() {
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelector('[data-remove-item]').addEventListener('click', () => {
            row.remove();
            refresh();
        });
        items.append(row);
        refresh();
    }
    form.querySelector('[data-add-item]').addEventListener('click', addItem);
    items.addEventListener('input', refresh);
    payment.reset({ amount: 0, issue_date: form.elements.issue_date.value });
    form.elements.issue_date.addEventListener('input', () => payment.setIssueDate(form.elements.issue_date.value));
    addItem();
}
