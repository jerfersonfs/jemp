import { showFeedback } from '../components/feedback.js';

const page = document.querySelector('[data-invoice-page]');

if (page) {
    const filterForm = page.querySelector('[data-invoice-filter]');
    const rows = [...page.querySelectorAll('[data-table-row]')];
    const noResults = page.querySelector('[data-invoice-no-results]');
    const feedback = page.querySelector('[data-invoice-feedback]');
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
            if (layer) openDialog(layer, trigger);
        });
    });

    dialogLayers.forEach((layer) => {
        layer.addEventListener('click', (event) => {
            if (event.target === layer || event.target.closest('[data-dialog-dismiss], .jemp-dialog__close')) closeDialog();
        });
        layer.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = feedback?.querySelector('.jemp-notice__message');
                if (feedback) feedback.hidden = false;
                if (message) message.textContent = 'Campos validados. Os dados serão persistidos quando o backend estiver integrado.';
                closeDialog();
                showFeedback('Campos validados. Nenhuma nota foi salva no banco; o backend ainda será integrado.', 'info');
            });
            form.querySelectorAll('input[type="number"]').forEach((input) => {
                input.min = '0.01';
                input.step = '0.01';
            });
            const issueDate = form.querySelector('input[name="issue_date"]');
            const dueDate = form.querySelector('input[name="due_date"]');
            const paymentDate = form.querySelector('input[name="payment_date"]');
            const status = form.querySelector('select[name="status"]');
            if (issueDate?.value) {
                if (dueDate) dueDate.min = issueDate.value;
                if (paymentDate) paymentDate.min = issueDate.value;
            }
            const syncPaymentRequirement = () => {
                if (paymentDate) paymentDate.required = status?.value === 'Paga';
            };
            status?.addEventListener('change', syncPaymentRequirement);
            syncPaymentRequirement();
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
