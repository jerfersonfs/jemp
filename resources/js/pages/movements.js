function initializeMovementList(list) {
    const panel = list.closest('.content-panel');
    const pageRoot = list.closest('.jemp-shell__page') ?? document;
    const table = list.querySelector('.jemp-table');
    const tbody = table?.querySelector('tbody');
    const rows = [...(tbody?.querySelectorAll('tr[data-table-row]') ?? [])];
    tbody?.querySelector('tr:not([data-table-row])')?.remove();
    const emptyRow = document.createElement('tr');
    const emptyCell = document.createElement('td');
    const formDialog = pageRoot.querySelector('[data-movement-dialog="form"]');
    const detailDialog = pageRoot.querySelector('[data-movement-dialog="details"]');
    const recordForm = formDialog?.querySelector('form');
    const search = panel?.querySelector('[data-movement-search]');
    const filters = [...(panel?.querySelectorAll('[data-movement-filter]') ?? [])];
    const previous = list.querySelector('[data-page-previous]');
    const next = list.querySelector('[data-page-next]');
    const current = list.querySelector('[data-page-current]');
    const summary = list.querySelector('[data-movement-summary]');
    const pageSize = Number(list.dataset.pageSize) || 10;
    let page = 1;
    let activeLayer = null;
    let activeTrigger = null;

    emptyCell.colSpan = table?.querySelectorAll('thead th').length || 1;
    emptyCell.className = 'jemp-table__empty';
    emptyCell.textContent = 'Nenhum registro corresponde aos filtros selecionados.';
    emptyRow.append(emptyCell);
    emptyRow.hidden = true;
    tbody?.append(emptyRow);

    const parseRecord = (row) => {
        try {
            return JSON.parse(row.dataset.record ?? '{}');
        } catch {
            return {};
        }
    };

    const closeDialog = () => {
        if (!activeLayer) return;
        activeLayer.hidden = true;
        document.body.classList.remove('movement-dialog-open');
        activeTrigger?.focus();
        activeLayer = null;
        activeTrigger = null;
    };

    const openDialog = (layer, trigger) => {
        if (!layer) return;
        if (activeLayer) closeDialog();
        activeLayer = layer.closest('.movement-dialog-layer');
        activeTrigger = trigger;
        activeLayer.hidden = false;
        document.body.classList.add('movement-dialog-open');
        const focusTarget = activeLayer.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([data-dialog-dismiss])');
        (focusTarget ?? layer)?.focus();
    };

    const displayValue = (key, value) => {
        const isoDate = key === 'date' && String(value).match(/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}:\d{2}))?/);
        if (isoDate) {
            const [, year, month, day, time] = isoDate;
            return `${day}/${month}/${year}${time ? ` ${time}` : ''}`;
        }
        return String(value ?? '—');
    };

    const update = () => {
        const query = (search?.value ?? '').trim().toLocaleLowerCase('pt-BR');
        const selectedFilters = Object.fromEntries(filters.map((filter) => [filter.dataset.movementFilter, filter.value]));
        const matchingRows = rows.filter((row) => {
            const record = parseRecord(row);
            const matchesSearch = !query || JSON.stringify(record).toLocaleLowerCase('pt-BR').includes(query);
            const matchesFilters = Object.entries(selectedFilters).every(([key, value]) => !value || row.dataset[key] === value);
            return matchesSearch && matchesFilters;
        });
        const pages = Math.max(1, Math.ceil(matchingRows.length / pageSize));
        page = Math.min(page, pages);
        const firstVisible = (page - 1) * pageSize;
        matchingRows.forEach((row, index) => { row.hidden = index < firstVisible || index >= firstVisible + pageSize; });
        rows.filter((row) => !matchingRows.includes(row)).forEach((row) => { row.hidden = true; });
        emptyRow.hidden = matchingRows.length > 0;
        if (current) current.textContent = `${page} / ${pages}`;
        if (previous) previous.disabled = page <= 1;
        if (next) next.disabled = page >= pages;
        if (summary) {
            const first = matchingRows.length ? firstVisible + 1 : 0;
            const last = Math.min(firstVisible + pageSize, matchingRows.length);
            summary.textContent = `Mostrando ${first}–${last} de ${matchingRows.length} ${list.dataset.resourceLabel ?? 'registros'}`;
        }
    };

    const showDetails = (row, trigger) => {
        if (!detailDialog) return;
        const record = parseRecord(row);
        const title = detailDialog.querySelector('.jemp-dialog__title');
        const grid = detailDialog.querySelector('.jemp-details-dialog__grid');
        const headers = [...(table?.querySelectorAll('thead th') ?? [])];
        const labels = new Map();
        const fallbackLabels = { code: 'Código', date: 'Data', type: 'Tipo', product: 'Produto', quantity: 'Quantidade', origin: 'Origem', destination: 'Destino', warehouse: 'Almoxarifado', recipient: 'Destinatário', justification: 'Justificação', condition: 'Condição' };
        row.querySelectorAll('td[data-column-key]').forEach((cell, index) => {
            if (cell.dataset.columnType !== 'actions') labels.set(cell.dataset.columnKey, headers[index]?.textContent.trim() || cell.dataset.columnKey);
        });
        recordForm?.querySelectorAll('.jemp-form-dialog__field').forEach((field) => {
            const label = field.querySelector('label');
            const control = field.querySelector('[name]');
            if (label && control) labels.set(control.name, label.textContent.trim());
        });
        if (title) title.textContent = `Detalhes do registro ${record.code ?? ''}`.trim();
        if (grid) {
            grid.replaceChildren();
            Object.entries(record).forEach(([key, value]) => {
                if (key.startsWith('_') || key === 'id') return;
                const item = document.createElement('div');
                const term = document.createElement('dt');
                const description = document.createElement('dd');
                item.className = 'jemp-details-dialog__item';
                term.textContent = labels.get(key) ?? fallbackLabels[key] ?? key;
                description.textContent = displayValue(key, value);
                item.append(term, description);
                grid.append(item);
            });
        }
        openDialog(detailDialog, trigger);
    };

    pageRoot.addEventListener('click', (event) => {
        const createTrigger = event.target.closest('[data-dialog-open="movement-form"]');
        if (createTrigger) {
            recordForm?.reset();
            const dateField = recordForm?.elements.date;
            if (dateField) {
                const localDate = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString();
                dateField.value = dateField.type === 'datetime-local' ? localDate.slice(0, 16) : localDate.slice(0, 10);
            }
            openDialog(formDialog, createTrigger);
            return;
        }

        const viewTrigger = event.target.closest('[data-movement-action="view"]');
        if (viewTrigger) {
            const row = viewTrigger.closest('tr[data-table-row]');
            if (row) showDetails(row, viewTrigger);
            return;
        }

        const layer = event.target.closest('.movement-dialog-layer');
        if (layer && (event.target === layer || event.target.closest('[data-dialog-dismiss]'))) closeDialog();
    });

    search?.addEventListener('input', () => { page = 1; update(); });
    filters.forEach((filter) => filter.addEventListener('change', () => { page = 1; update(); }));
    previous?.addEventListener('click', () => { page = Math.max(1, page - 1); update(); });
    next?.addEventListener('click', () => { page += 1; update(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && activeLayer) closeDialog();
    });
    update();
}

document.querySelectorAll('[data-movement-list]').forEach(initializeMovementList);
