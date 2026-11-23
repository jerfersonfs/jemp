import { requestJson } from '../components/loading.js';
import { initializeManualInvoice } from './sale-payment.js';
import { initializeInvoiceImport } from './invoice-import.js';
import { reloadWithFeedback, showFeedback } from '../components/feedback.js';

const page = document.querySelector('[data-invoice-page]');

if (page) {
    initializeInvoiceImport(page);
    initializeManualInvoice(page);
    const filterForm = page.querySelector('[data-invoice-filter]');
    const rows = [...page.querySelectorAll('.invoice-table [data-table-row]')];
    const noResults = page.querySelector('[data-invoice-no-results]');
    const dialogLayers = [...page.querySelectorAll('[data-dialog-layer]')];
    const previousButton = page.querySelector('[data-invoice-previous]');
    const nextButton = page.querySelector('[data-invoice-next]');
    const pageSize = 10;
    let currentPage = 1;
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

        const totalPages = Math.max(1, Math.ceil(visibleRows.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * pageSize;
        visibleRows.forEach((row, index) => { row.hidden = index < start || index >= start + pageSize; });
        previousButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;
        page.querySelector('[data-invoice-current]').textContent = currentPage + ' / ' + totalPages;
        page.querySelector('[data-invoice-pagination-summary]').textContent =
            'Mostrando ' + (visibleRows.length ? start + 1 : 0) + '–' + Math.min(start + pageSize, visibleRows.length) + ' de ' + visibleRows.length + ' notas fiscais';
        if (noResults) noResults.hidden = visibleRows.length > 0 || rows.length === 0;
        updateSummary(visibleRows);
    }

    function resetPagination() {
        currentPage = 1;
        applyFilters();
    }

    previousButton.addEventListener('click', () => { currentPage = Math.max(1, currentPage - 1); applyFilters(); });
    nextButton.addEventListener('click', () => { currentPage += 1; applyFilters(); });
    filterForm?.addEventListener('input', resetPagination);
    filterForm?.addEventListener('change', resetPagination);
    filterForm?.addEventListener('submit', (event) => { event.preventDefault(); resetPagination(); });
    filterForm?.addEventListener('reset', () => window.requestAnimationFrame(resetPagination));

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
        const layer = dialogLayers.find((candidate) => candidate.dataset.dialogLayer === trigger.dataset.dialogOpen);
        if (layer) trigger.addEventListener('click', () => openDialog(layer, trigger));
    });

    dialogLayers.forEach((layer) => layer.addEventListener('click', (event) => {
        if (event.target.closest('[data-dialog-dismiss]')) closeDialog();
    }));

    page.querySelectorAll('[data-financial-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            if (button.disabled) return;
            button.disabled = true;
            try {
                const { response, data } = await requestJson(form.action, {
                    method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form),
                });
                if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message);
                reloadWithFeedback(data.message || 'Nota fiscal cadastrada.');
            } catch (error) {
                showFeedback(error.message, 'error');
                button.disabled = false;
            }
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

    applyFilters();
}
