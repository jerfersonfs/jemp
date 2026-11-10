import { showFeedback } from '../components/feedback.js';

const page = document.querySelector('[data-invoice-page]');

if (page) {
    const importLayer = page.querySelector('[data-invoice-import-layer]');
    const importForm = page.querySelector('[data-invoice-import-form]');
    const importFeedback = page.querySelector('[data-invoice-import-feedback]');
    const openImport = page.querySelector('[data-invoice-import-open]');
    let importFeedbackTimer;
    const setImportFeedback = (message, isError = false) => {
        window.clearTimeout(importFeedbackTimer);
        importFeedback.textContent = message;
        importFeedback.hidden = !message;
        importFeedback.dataset.error = String(isError);
        if (message) importFeedbackTimer = window.setTimeout(() => { importFeedback.hidden = true; }, 15_000);
    };
    openImport?.addEventListener('click', () => { setImportFeedback(''); importLayer.hidden = false; importForm.querySelector('input[type="file"]').focus(); });
    page.querySelectorAll('[data-invoice-import-close]').forEach(button => button.addEventListener('click', () => { importLayer.hidden = true; openImport?.focus(); }));
    importForm?.addEventListener('submit', async (event) => {
        event.preventDefault(); setImportFeedback('Enviando XML...');
        try {
            const response = await fetch('/api/invoices/import', { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(importForm) });
            const result = await response.json();
            if (!response.ok) throw new Error(result.details || Object.values(result.errors || {}).flat().join(' ') || result.message || 'Falha ao importar o XML.');
            setImportFeedback(result.message || 'NF-e importada com sucesso.');
            sessionStorage.setItem('invoice-page-feedback', result.message || 'NF-e importada com sucesso.');
            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) { setImportFeedback(error.message, true); }
    });
    const pendingFeedback = sessionStorage.getItem('invoice-page-feedback');
    if (pendingFeedback) {
        sessionStorage.removeItem('invoice-page-feedback');
        showFeedback(pendingFeedback, 'success');
    }
    const filterForm = page.querySelector('[data-invoice-filter]');
    const rows = [...page.querySelectorAll('[data-table-row]')];
    const noResults = page.querySelector('[data-invoice-no-results]');
    const dialogLayers = [...page.querySelectorAll('[data-dialog-layer]')];
    let activeLayer = null;
    let activeTrigger = null;
    let fallbackTrigger = null;

    const normalize = (value) => (value || '')
        .toLocaleLowerCase('pt-BR')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    function parseLocalDate(value) {
        if (!value) return null;
        const [year, month, day] = value.split('-').map(Number);
        if (!year || !month || !day) return null;
        return new Date(year, month - 1, day);
    }

    function parseDisplayDate(value) {
        const match = (value || '').trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!match) return null;
        return new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
    }

    function updateSummary(visibleRows) {
        const counts = { all: visibleRows.length, due: 0, overdue: 0, paid: 0 };
        visibleRows.forEach((row) => {
            const status = normalize(row.querySelector('.jemp-status-badge')?.textContent);
            if (status.includes('a vencer')) counts.due += 1;
            if (status.includes('vencida')) counts.overdue += 1;
            if (status.includes('paga')) counts.paid += 1;
        });
        Object.entries(counts).forEach(([key, value]) => {
            const output = page.querySelector('[data-invoice-count="' + key + '"]');
            if (output) output.textContent = String(value);
        });
    }

    function applyFilters() {
        if (!filterForm) return;
        const search = normalize(filterForm.elements.search?.value.trim());
        const selectedStatus = filterForm.elements.status?.value || 'all';
        const fromDate = parseLocalDate(filterForm.elements.from?.value);
        const toDate = parseLocalDate(filterForm.elements.to?.value);
        const visibleRows = [];

        rows.forEach((row) => {
            const searchText = normalize(row.textContent + ' ' + (row.dataset.searchText || ''));
            const statusText = row.querySelector('.jemp-status-badge')?.textContent.trim() || '';
            const issueDate = parseDisplayDate(row.querySelector('.invoice-issued-date')?.textContent);
            const matchesSearch = !search || searchText.includes(search);
            const matchesStatus = selectedStatus === 'all' || normalize(statusText) === normalize(selectedStatus);
            const matchesFrom = !fromDate || (issueDate && issueDate >= fromDate);
            const matchesTo = !toDate || (issueDate && issueDate <= toDate);
            const matches = matchesSearch && matchesStatus && matchesFrom && matchesTo;
            row.hidden = !matches;
            if (matches) visibleRows.push(row);
        });

        if (noResults) noResults.hidden = visibleRows.length > 0;
        updateSummary(visibleRows);
    }

    filterForm?.addEventListener('input', applyFilters);
    filterForm?.addEventListener('change', applyFilters);
    filterForm?.addEventListener('submit', (event) => { event.preventDefault(); applyFilters(); });
    filterForm?.addEventListener('reset', () => window.requestAnimationFrame(applyFilters));

    function closeDialog({ restoreFocus = true } = {}) {
        if (!activeLayer) return;
        activeLayer.hidden = true;
        document.body.classList.remove('invoice-dialog-open');
        if (restoreFocus) {
            const triggerIsHidden = activeTrigger?.closest("[data-dialog-layer]")?.hidden;
            (triggerIsHidden ? fallbackTrigger : activeTrigger)?.focus();
        }
        activeLayer = null;
        activeTrigger = null;
        fallbackTrigger = null;
    }

    function openDialog(layer, trigger) {
        if (activeLayer) {
            const previousTrigger = activeTrigger;
            closeDialog({ restoreFocus: false });
            fallbackTrigger = previousTrigger;
        }
        activeLayer = layer;
        activeTrigger = trigger;
        layer.hidden = false;
        document.body.classList.add('invoice-dialog-open');
        const candidates = [...layer.querySelectorAll('input:not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled]), button:not([data-dialog-dismiss])')];
        const focusTarget = candidates.find((element) => element.offsetParent !== null);
        (focusTarget ?? layer.querySelector('[role="dialog"], [role="alertdialog"]'))?.focus();
    }

    page.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const target = trigger.dataset.dialogOpen;
            const layer = dialogLayers.find((candidate) => candidate.dataset.dialogLayer === target);
            if (layer) {
                if (layer.querySelector('[data-invoice-item-form]')) {
                    const form = layer.querySelector('[data-invoice-item-form]');
                    form?.reset();
                    delete layer.dataset.itemId;
                    layer.querySelector('[data-item-dialog-title]').textContent = 'Adicionar item à nota';
                    layer.querySelector('[data-item-submit-label]').textContent = 'Cadastrar item';
                    syncItemPrice(form);
                    layer.dataset.invoiceMode = 'item';
                }
                openDialog(layer, trigger);
            }
        });
    });

    function syncItemPrice(form) {
        if (!form) return;
        const product = form.elements.product_id?.selectedOptions[0];
        const price = form.elements.unit_price;
        if (price) price.value = product?.dataset.unitPrice ?? '';
    }

    page.querySelectorAll('[data-invoice-item-form]').forEach((form) => {
        form.elements.product_id?.addEventListener('change', () => syncItemPrice(form));
    });

    page.addEventListener('click', async (event) => {
        const action = event.target.closest('[data-invoice-item-action]');
        if (!action) return;
        const detailLayer = action.closest('[data-dialog-layer]');
        const itemLayerId = detailLayer?.querySelector('[data-dialog-open][data-dialog-open^="invoice-add-item-"]')?.dataset.dialogOpen;
        const itemLayer = dialogLayers.find((layer) => layer.dataset.dialogLayer === itemLayerId);
        if (!itemLayer) return;

        if (action.dataset.invoiceItemAction === 'edit') {
            const form = itemLayer.querySelector('[data-invoice-item-form]');
            form.elements.product_id.value = action.dataset.productId;
            form.elements.quantity.value = action.dataset.quantity;
            form.elements.unit_price.value = action.dataset.unitPrice;
            itemLayer.dataset.itemId = action.dataset.itemId;
            itemLayer.dataset.invoiceMode = 'item-edit';
            itemLayer.querySelector('[data-item-dialog-title]').textContent = 'Editar item da nota';
            itemLayer.querySelector('[data-item-submit-label]').textContent = 'Salvar alterações';
            openDialog(itemLayer, action);
            return;
        }

        if (!window.confirm(`Excluir o item “${action.dataset.itemLabel}” desta nota fiscal?`)) return;
        try {
            const response = await fetch(`/api/invoice-items/${action.dataset.itemId}`, { method: 'DELETE', headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Não foi possível excluir o item.');
            sessionStorage.setItem('invoice-page-feedback', 'Item excluído da nota fiscal.');
            window.location.reload();
        } catch (error) {
            showFeedback(error.message, 'error');
        }
    });

    dialogLayers.forEach((layer) => {
        layer.addEventListener('click', (event) => {
            if (event.target === layer || event.target.closest('[data-dialog-dismiss], .jemp-dialog__close')) closeDialog();
        });
        layer.querySelectorAll('form').forEach((form) => {
            const paymentStatus = form.querySelector('[name="payment_status"]');
            const paymentDate = form.querySelector('[name="payment_date"]');
            const paymentDateField = paymentDate?.closest('.jemp-form-dialog__field');
            const customerSelect = form.querySelector('[name="customer_id"]');
            const customerDocument = form.querySelector('[name="customer_document_preview"]');
            const syncCustomerDocument = () => {
                if (customerDocument) customerDocument.value = customerSelect?.selectedOptions[0]?.dataset.customerDocument || '';
            };
            const syncPaymentDate = () => {
                const isPaid = paymentStatus?.value === 'Paga';
                if (paymentDateField) paymentDateField.hidden = !isPaid;
                if (paymentDate) {
                    paymentDate.required = isPaid;
                    if (!isPaid) paymentDate.value = '';
                }
            };
            customerSelect?.addEventListener('change', syncCustomerDocument);
            paymentStatus?.addEventListener('change', syncPaymentDate);
            syncCustomerDocument();
            syncPaymentDate();

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const invoiceMode = layer.dataset.invoiceMode;
                const payload = Object.fromEntries(new FormData(form).entries());
                if (['item', 'item-edit'].includes(invoiceMode)) delete payload.unit_price;
                const invoiceId = layer.dataset.invoiceId;
                const endpoint = invoiceMode === 'item-edit'
                    ? `/api/invoice-items/${layer.dataset.itemId}`
                    : invoiceMode === 'item' ? '/api/invoice-items'
                    : invoiceMode === 'edit' ? `/api/invoices/${invoiceId}` : '/api/invoices';
                const method = ['edit', 'item-edit'].includes(invoiceMode) ? 'PUT' : 'POST';

                try {
                    const response = await fetch(endpoint, {
                        method,
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const validationMessage = Object.values(result.errors || {}).flat().join(' ');
                        throw new Error(validationMessage || result.message || 'Não foi possível salvar a nota fiscal.');
                    }

                    const message = ['item', 'item-edit'].includes(invoiceMode)
                        ? invoiceMode === 'item-edit' ? 'Item da nota fiscal atualizado com sucesso.' : 'Item adicionado à nota fiscal com sucesso.'
                        : invoiceMode === 'edit' ? 'Nota fiscal atualizada com sucesso.' : 'Nota fiscal cadastrada com sucesso.';
                    sessionStorage.setItem('invoice-page-feedback', message);
                    closeDialog();
                    window.location.reload();
                } catch (error) {
                    showFeedback(error.message, 'error');
                }
            });
        });
    });

    document.addEventListener('keydown', (event) => {
        if (!activeLayer) return;
        if (event.key === 'Escape') { event.preventDefault(); closeDialog(); return; }
        if (event.key !== 'Tab') return;
        const focusable = [...activeLayer.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])')]
            .filter((element) => element.offsetParent !== null && !element.hasAttribute('data-dialog-dismiss'));
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });

    page.querySelectorAll('.jemp-notice__close').forEach((button) => {
        button.addEventListener('click', () => button.closest('.jemp-notice')?.remove());
    });
    applyFilters();
}
