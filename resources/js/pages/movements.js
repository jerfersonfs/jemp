import { showFeedback } from '../components/feedback.js';

function initializeMovementList(list) {
    const panel = list.closest('.content-panel');
    const pageRoot = list.closest('.jemp-shell__page') ?? document;
    const table = list.querySelector('.jemp-table');
    const tbody = table?.querySelector('tbody');
    const initialRows = [...(tbody?.querySelectorAll('tr[data-table-row]') ?? [])];
    const rowTemplate = initialRows[0]?.cloneNode(true);
    const emptyRow = document.createElement('tr');
    const emptyCell = document.createElement('td');
    const formDialog = pageRoot.querySelector('[data-movement-dialog="form"]');
    const detailDialog = pageRoot.querySelector('[data-movement-dialog="details"]');
    const confirmDialog = pageRoot.querySelector('[data-movement-dialog="confirm"]');
    const recordForm = formDialog?.querySelector('form');
    const search = panel?.querySelector('[data-movement-search]');
    const filters = [...(panel?.querySelectorAll('[data-movement-filter]') ?? [])];
    const previous = list.querySelector('[data-page-previous]');
    const next = list.querySelector('[data-page-next]');
    const current = list.querySelector('[data-page-current]');
    const summary = list.querySelector('[data-movement-summary]');
    const pageSize = Number(list.dataset.pageSize) || 10;
    let rows = initialRows;
    let page = 1;
    let activeLayer = null;
    let activeTrigger = null;
    let activeRow = null;
    let editMode = false;

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
        const focusTarget = activeLayer.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([data-dialog-dismiss]), [tabindex]:not([tabindex="-1"])');
        (focusTarget ?? layer)?.focus();
    };

    const setFormMode = (isEditing, record = {}) => {
        if (!formDialog || !recordForm) return;
        editMode = isEditing;
        recordForm.reset();
        const dialogTitle = formDialog.querySelector('.jemp-dialog__title');
        const submitButton = recordForm.querySelector('[type="submit"]');
        formDialog.classList.toggle('jemp-dialog--editing', isEditing);
        recordForm.classList.toggle('jemp-form-dialog--edit', isEditing);
        recordForm.classList.toggle('jemp-form-dialog--create', !isEditing);
        if (dialogTitle) dialogTitle.textContent = isEditing ? formDialog.dataset.editTitle : formDialog.dataset.createTitle;
        if (submitButton) submitButton.textContent = isEditing ? formDialog.dataset.editLabel : formDialog.dataset.createLabel;

        for (const field of recordForm.elements) {
            if (!field.name || !(field.name in record)) continue;
            if (field.type === 'date' || field.type === 'datetime-local') {
                field.value = toDateInputValue(record[field.name], field.type);
            } else if (field.type === 'number') {
                field.value = String(record[field.name] ?? '').match(/-?\d+(?:[.,]\d+)?/)?.[0]?.replace(',', '.') ?? '';
            } else {
                field.value = record[field.name];
            }
        }
    };

    const toDateInputValue = (value, type = 'date') => {
        if (!value) return '';
        const text = String(value);
        if (type === 'datetime-local') {
            const isoMatch = text.match(/^(\d{4}-\d{2}-\d{2})(?:[T ](\d{2}:\d{2}))?/);
            if (isoMatch) return `${isoMatch[1]}T${isoMatch[2] ?? '00:00'}`;
            const localMatch = text.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}:\d{2}))?/);
            return localMatch ? `${localMatch[3]}-${localMatch[2]}-${localMatch[1]}T${localMatch[4] ?? '00:00'}` : '';
        }
        if (/^\d{4}-\d{2}-\d{2}/.test(text)) return text.slice(0, 10);
        const match = text.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        return match ? `${match[3]}-${match[2]}-${match[1]}` : '';
    };

    const displayValue = (key, value) => {
        const isoDate = key === 'date' && String(value).match(/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}:\d{2}))?/);
        if (isoDate) {
            const [, year, month, day, time] = isoDate;
            return `${day}/${month}/${year}${time ? ` ${time}` : ''}`;
        }
        return String(value ?? '—');
    };

    const updateRow = (row, record) => {
        row.dataset.record = JSON.stringify(record);
        for (const filter of filters) {
            row.dataset[filter.dataset.movementFilter] = String(record[filter.dataset.movementFilter] ?? '');
        }

        for (const cell of row.querySelectorAll('td[data-column-key]')) {
            if (cell.dataset.columnType === 'actions') {
                for (const button of cell.querySelectorAll('[data-movement-action]')) {
                    const actionLabel = button.dataset.movementAction === 'view' ? 'Visualizar' : button.dataset.movementAction === 'edit' ? 'Editar' : 'Excluir';
                    button.setAttribute('aria-label', `${actionLabel}: ${record.code ?? ''}`);
                    button.title = actionLabel;
                }
                continue;
            }

            cell.textContent = displayValue(cell.dataset.columnKey, record[cell.dataset.columnKey]);
        }
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
            summary.textContent = `Mostrando ${first}–${last} de ${matchingRows.length} ${list.dataset.resourceLabel ?? 'registros'} demonstrativos`;
        }
    };

    const showDetails = (row, trigger) => {
        if (!detailDialog) return;
        const record = parseRecord(row);
        const title = detailDialog.querySelector('.jemp-dialog__title');
        const grid = detailDialog.querySelector('.jemp-details-dialog__grid');
        const headers = [...(table?.querySelectorAll('thead th') ?? [])];
        const labels = new Map();
        const fallbackLabels = {
            code: 'Código',
            date: 'Data',
            type: 'Tipo',
            product: 'Produto',
            lot: 'Lote',
            unit: 'Unidade',
            stock: 'Estoque atual',
            quantity: 'Quantidade',
            remaining: 'Estoque restante',
            warehouse: 'Almoxarifado',
            recipient: 'Destinatário',
            reference: 'Documento de referência',
            responsible: 'Responsável',
            notes: 'Observações',
            origin: 'Origem',
            destination: 'Destino',
            condition: 'Condição',
        };
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
                if (key.startsWith('_')) return;
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

    const generateCode = () => {
        const codes = rows.map((row) => String(parseRecord(row).code ?? '')).map((code) => Number(code.replace(/\D/g, '')) || 0);
        const nextCode = Math.max(0, ...codes) + 1;
        return list.dataset.resourceLabel === 'saídas' ? String(nextCode).padStart(6, '0') : `#${nextCode}`;
    };

    const saveRecord = () => {
        if (!recordForm || !rowTemplate || !tbody) return;
        const formData = Object.fromEntries(new FormData(recordForm).entries());
        if (formData.date) formData.date = displayValue('date', formData.date);
        const wasEditing = editMode && Boolean(activeRow);
        const previousRecord = editMode && activeRow ? parseRecord(activeRow) : {};
        const record = { ...previousRecord, ...formData };
        if ('quantity' in formData && !('unit' in formData)) {
            const unit = String(previousRecord.quantity ?? '').match(/\s+(.+)$/)?.[1] ?? (list.dataset.resourceLabel === 'saídas' ? '' : 'un.');
            record.quantity = `${formData.quantity}${unit ? ` ${unit}` : ''}`;
        }
        if (!record.code) record.code = generateCode();
        if ('stock' in record && 'quantity' in record) {
            record.remaining = String(Math.max(0, (Number(record.stock) || 0) - (Number(record.quantity) || 0)));
        }

        let row = activeRow;
        if (!editMode || !row) {
            row = rowTemplate.cloneNode(true);
            row.hidden = false;
            rows.push(row);
            tbody.insertBefore(row, emptyRow);
        }
        updateRow(row, record);
        activeRow = null;
        page = 1;
        update();
        closeDialog();
        showFeedback(`Movimentação ${wasEditing ? 'atualizada' : 'adicionada'} na demonstração. A alteração não foi salva no banco.`);
    };

    pageRoot.addEventListener('click', (event) => {
        const createTrigger = event.target.closest('[data-dialog-open="movement-form"]');
        if (createTrigger) {
            activeRow = null;
            setFormMode(false);
            openDialog(formDialog, createTrigger);
            return;
        }

        const actionTrigger = event.target.closest('[data-movement-action]');
        if (actionTrigger) {
            const row = actionTrigger.closest('tr[data-table-row]');
            if (!row) return;
            const action = actionTrigger.dataset.movementAction;
            if (action === 'view') showDetails(row, actionTrigger);
            if (action === 'edit') {
                activeRow = row;
                setFormMode(true, parseRecord(row));
                openDialog(formDialog, actionTrigger);
            }
            if (action === 'delete') {
                activeRow = row;
                const record = parseRecord(row);
                const message = confirmDialog?.querySelector('.jemp-confirmation__message');
                if (message) message.textContent = `Tem certeza de que deseja excluir o registro ${record.code ?? ''} — ${record.product ?? ''}? Essa ação não poderá ser desfeita.`;
                openDialog(confirmDialog, actionTrigger);
            }
            return;
        }

        const layer = event.target.closest('.movement-dialog-layer');
        if (layer && (event.target === layer || event.target.closest('[data-dialog-dismiss]'))) {
            closeDialog();
            return;
        }

        if (event.target.closest('[data-movement-confirm]') && activeRow) {
            const removedCode = parseRecord(activeRow).code ?? '';
            activeRow.remove();
            rows = rows.filter((row) => row !== activeRow);
            activeRow = null;
            update();
            closeDialog();
            showFeedback(`Movimentação ${removedCode} removida da demonstração. A alteração não foi salva no banco.`);
        }
    });

    recordForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!recordForm.reportValidity()) return;
        saveRecord();
    });
    search?.addEventListener('input', () => { page = 1; update(); });
    filters.forEach((filter) => filter.addEventListener('change', () => { page = 1; update(); }));
    previous?.addEventListener('click', () => { page = Math.max(1, page - 1); update(); });
    next?.addEventListener('click', () => { page += 1; update(); });

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

    rows.forEach((row) => updateRow(row, parseRecord(row)));
    update();
}

document.querySelectorAll('[data-movement-list]').forEach(initializeMovementList);
