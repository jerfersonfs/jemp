const openButtons = document.querySelectorAll('[data-dialog-open]');
let activeLayer = null;
let activeTrigger = null;

function closeDialog() {
    if (!activeLayer) return;
    activeLayer.hidden = true;
    document.body.classList.remove('preview-dialog-open');
    activeTrigger?.focus();
    activeLayer = null;
    activeTrigger = null;
}

function openDialog(layer, trigger) {
    if (activeLayer) closeDialog();
    activeLayer = layer;
    activeTrigger = trigger;
    layer.hidden = false;
    document.body.classList.add('preview-dialog-open');
    const focusTarget = layer.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([data-dialog-dismiss]), a[href], [tabindex]:not([tabindex="-1"])');
    (focusTarget ?? layer.querySelector('[role="dialog"], [role="alertdialog"]'))?.focus();
}

openButtons.forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const layer = document.querySelector(`[data-dialog-layer="${CSS.escape(trigger.dataset.dialogOpen)}"]`);
        if (layer) openDialog(layer, trigger);
    });
});

document.querySelectorAll('[data-dialog-layer]').forEach((layer) => {
    layer.addEventListener('click', (event) => {
        if (event.target === layer || event.target.closest('[data-dialog-dismiss]')) closeDialog();
        if (event.target.closest('.jemp-dialog__close, .jemp-confirmation__button--cancel')) closeDialog();
        if (event.target.closest('.jemp-confirmation__button--confirm, .jemp-dialog__footer .jemp-dialog__button--primary')) closeDialog();
    });

    layer.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            closeDialog();
        });
    });
});

document.addEventListener('keydown', (event) => {
    if (!activeLayer) return;
    if (event.key === 'Escape') {
        event.preventDefault();
        closeDialog();
        return;
    }
    if (event.key !== 'Tab') return;

    const focusable = [...activeLayer.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])')]
        .filter((element) => element.offsetParent !== null && !element.hasAttribute('data-dialog-dismiss'));
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

document.querySelectorAll('.jemp-notice__close').forEach((button) => {
    button.addEventListener('click', () => button.closest('.jemp-notice')?.remove());
});

document.querySelectorAll('[data-preview-table]').forEach((tableRegion) => {
    const rows = [...tableRegion.querySelectorAll('[data-table-row]')];
    const filterForm = tableRegion.querySelector('[data-preview-filter]');
    const pageSize = Number(tableRegion.dataset.pageSize) || 5;
    const summary = tableRegion.querySelector('[data-pagination-summary]');
    const previous = tableRegion.querySelector('[data-page-prev]');
    const next = tableRegion.querySelector('[data-page-next]');
    const current = tableRegion.querySelector('[data-page-current]');
    let page = 1;
    let filteredRows = rows;

    function applyFilters() {
        if (!filterForm) return;
        const search = (filterForm.querySelector('[name="table-search"]')?.value || '').trim().toLowerCase();
        const status = filterForm.querySelector('[name="table-status"]')?.value || 'all';
        const statusText = { paid: 'pago', due: 'a vencer', overdue: 'vencido' }[status] || '';
        filteredRows = rows.filter((row) => {
            const matchesSearch = !search || row.textContent.toLowerCase().includes(search);
            const matchesStatus = status === 'all' || row.textContent.toLowerCase().includes(statusText);
            return matchesSearch && matchesStatus;
        });
        page = 1;
    }

    function render() {
        const pages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
        page = Math.min(page, pages);
        const start = (page - 1) * pageSize;
        rows.forEach((row) => { row.hidden = true; });
        filteredRows.slice(start, start + pageSize).forEach((row) => { row.hidden = false; });
        if (summary) summary.textContent = filteredRows.length ? `Mostrando ${start + 1}–${Math.min(start + pageSize, filteredRows.length)} de ${filteredRows.length} registros` : 'Nenhum registro encontrado';
        if (previous) previous.disabled = page <= 1;
        if (next) next.disabled = page >= pages;
        if (current) current.textContent = `${page} / ${pages}`;
    }

    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        applyFilters();
        render();
    });
    previous?.addEventListener('click', () => { page -= 1; render(); });
    next?.addEventListener('click', () => { page += 1; render(); });
    render();
});

document.querySelectorAll('[data-preview-filter]:not([data-preview-table-filter])').forEach((form) => {
    form.addEventListener('submit', (event) => event.preventDefault());
});
