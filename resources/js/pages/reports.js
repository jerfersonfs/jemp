const reportPage = document.querySelector('[data-reports-page]');

if (reportPage) {
    const config = JSON.parse(reportPage.dataset.reportConfig || '{}');
    const filterForm = reportPage.querySelector('[data-report-filter-form]');
    const tabs = reportPage.querySelector('[data-tabs="reports-tabs"]');
    const filterOptions = filterForm?.querySelector('.jemp-vertical-filter__options');
    const searchInput = filterForm?.querySelector('[type="search"]');
    const pageSize = 5;
    let activeSectionKey = 'overview';
    let currentPage = 1;
    let filteredRows = [];
    let activeDialog = null;
    let lastTrigger = null;

    const activePanel = () => reportPage.querySelector(`[data-tab-panel="${CSS.escape(activeSectionKey)}"]`);
    const presetValue = () => filterForm?.querySelector('[name="preset"]:checked')?.value || 'all';

    function selectedRows(section) {
        const search = (searchInput?.value || '').trim().toLocaleLowerCase('pt-BR');
        return section.rows.filter((row) => {
            const matchesPreset = presetValue() === 'all' || row.preset === presetValue();
            const matchesSearch = !search || Object.entries(row)
                .filter(([key]) => !['preset', 'detailTarget'].includes(key))
                .some(([, value]) => String(value).toLocaleLowerCase('pt-BR').includes(search));
            return matchesPreset && matchesSearch;
        });
    }

    function renderTable(tableRegion, columns, rows, statusVariants = {}) {
        const table = tableRegion?.querySelector('table');
        const head = table?.querySelector('thead');
        const body = table?.querySelector('tbody');
        if (!table || !head || !body) return;

        const headerRow = document.createElement('tr');
        columns.forEach((column) => {
            const cell = document.createElement('th');
            cell.scope = 'col';
            cell.textContent = column.label;
            headerRow.append(cell);
        });
        head.replaceChildren(headerRow);

        if (!rows.length) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.className = 'jemp-table__empty';
            emptyCell.colSpan = columns.length;
            emptyCell.textContent = 'Nenhum registro encontrado para estes filtros.';
            emptyRow.append(emptyCell);
            body.replaceChildren(emptyRow);
            return;
        }

        const fragment = document.createDocumentFragment();
        rows.forEach((row) => {
            const tableRow = document.createElement('tr');
            tableRow.dataset.tableRow = '';
            columns.forEach((column) => {
                const cell = document.createElement('td');
                cell.dataset.columnKey = column.key;
                if (column.type === 'status') {
                    const variant = statusVariants[row[column.key]] || 'neutral';
                    const badge = document.createElement('span');
                    badge.className = `jemp-status-badge jemp-status-badge--${variant}`;
                    const dot = document.createElement('span');
                    dot.className = 'jemp-status-badge__dot';
                    dot.setAttribute('aria-hidden', 'true');
                    const label = document.createElement('span');
                    label.textContent = row[column.key] ?? '—';
                    badge.append(dot, label);
                    cell.append(badge);
                } else if (column.type === 'detail') {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'jemp-table__detail';
                    button.dataset.reportRecord = JSON.stringify(row);
                    button.dataset.dialogOpen = 'report-record-details';
                    button.textContent = column.detailLabel || 'Ver';
                    cell.append(button);
                } else {
                    cell.textContent = row[column.key] ?? '—';
                }
                tableRow.append(cell);
            });
            fragment.append(tableRow);
        });
        body.replaceChildren(fragment);
    }

    function updatePresets(section) {
        if (!filterOptions) return;
        const legend = filterOptions.querySelector('legend');
        const options = section.presets.flatMap((preset, index) => {
            const label = document.createElement('label');
            label.className = 'jemp-vertical-filter__option';
            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'preset';
            input.value = preset.value;
            input.checked = preset.value === 'all';
            const text = document.createElement('span');
            text.textContent = preset.label;
            label.append(input, text);
            if (index === section.presets.length - 1) return [label];

            const separator = document.createElement('hr');
            separator.className = 'jemp-vertical-filter__separator';
            separator.setAttribute('aria-hidden', 'true');
            return [label, separator];
        });
        filterOptions.replaceChildren(...(legend ? [legend, ...options] : options));
    }

    function updateMetrics(section, panel) {
        panel.querySelectorAll('[data-report-metric]').forEach((card, index) => {
            const metric = section.metrics[index];
            if (!metric) return;
            const label = card.querySelector('.stat-card__label');
            const value = card.querySelector('.stat-card__value');
            if (label) label.textContent = metric.label;
            if (value) value.textContent = metric.value;
            card.dataset.metricSource = metric.source;
        });
    }

    function updatePagination(panel, rows) {
        const pageCount = Math.max(1, Math.ceil(rows.length / pageSize));
        currentPage = Math.min(currentPage, pageCount);
        const visibleRows = rows.slice((currentPage - 1) * pageSize, currentPage * pageSize);
        const section = config[activeSectionKey];
        renderTable(panel.querySelector('[data-report-table]'), section.columns, visibleRows, section.statusVariants);
        const start = rows.length ? (currentPage - 1) * pageSize + 1 : 0;
        const end = Math.min(currentPage * pageSize, rows.length);
        panel.querySelector('[data-report-summary]').textContent = `Mostrando ${start}–${end} de ${rows.length} registros demonstrativos`;
        panel.querySelector('[data-report-page]').textContent = `${currentPage} / ${pageCount}`;
        panel.querySelector('[data-report-prev]').disabled = currentPage <= 1;
        panel.querySelector('[data-report-next]').disabled = currentPage >= pageCount;
    }

    function applyFilters(resetPage = true) {
        const section = config[activeSectionKey];
        const panel = activePanel();
        if (!section || !panel) return;
        if (resetPage) currentPage = 1;
        filteredRows = selectedRows(section);
        updatePagination(panel, filteredRows);
    }

    function openDialog(layer, trigger) {
        if (!layer) return;
        if (activeDialog && activeDialog !== layer) activeDialog.hidden = true;
        activeDialog = layer;
        lastTrigger = trigger;
        layer.hidden = false;
        document.body.classList.add('reports-dialog-open');
        const focusTarget = layer.querySelector('[data-dialog-dismiss], button, input, select, textarea, [tabindex="0"]');
        (focusTarget || layer.querySelector('[role="dialog"]'))?.focus();
    }

    function fillRecordDialog(record) {
        const dialog = document.querySelector('[data-dialog-layer="report-record-details"] [role="dialog"]');
        const grid = dialog?.querySelector('.jemp-details-dialog__grid');
        if (!dialog || !grid) return;
        const columns = config[activeSectionKey].columns.filter((column) => column.type !== 'detail');
        const fragment = document.createDocumentFragment();
        columns.forEach((column) => {
            const item = document.createElement('div');
            item.className = 'jemp-details-dialog__item';
            const term = document.createElement('dt');
            term.textContent = column.label;
            const description = document.createElement('dd');
            description.textContent = record[column.key] ?? '—';
            item.append(term, description);
            fragment.append(item);
        });
        grid.replaceChildren(fragment);
        const heading = dialog.querySelector('.jemp-dialog__title');
        if (heading) heading.textContent = `Detalhes · ${record.product || record.number || record.code || record.module || record.user || 'registro'}`;
    }

    function metricComposition(source) {
        if (source.startsWith('movement-')) {
            const rows = config.movements.rows;
            const preset = source.replace('movement-', '');
            return { section: config.movements, rows: rows.filter((row) => row.preset === preset) };
        }
        if (source.startsWith('invoice-')) {
            const rows = config.invoices.rows;
            const operation = source.replace('invoice-', '') === 'entry' ? 'Entrada' : 'Saída';
            return { section: config.invoices, rows: rows.filter((row) => row.operation === operation) };
        }
        const sectionKey = activeSectionKey === 'overview' ? 'stock' : activeSectionKey;
        const section = config[sectionKey];
        if (!section) return { section: config.overview, rows: [] };
        if (['low', 'zero', 'normal'].includes(source)) {
            return { section, rows: section.rows.filter((row) => row.preset === source) };
        }
        if (['pending', 'authorized', 'canceled'].includes(source)) {
            return { section: config.invoices, rows: config.invoices.rows.filter((row) => row.preset === source) };
        }
        if (['entry', 'exit', 'internal'].includes(source) && activeSectionKey === 'movements') {
            return { section: config.movements, rows: config.movements.rows.filter((row) => row.preset === source) };
        }
        if (['update', 'created', 'read'].includes(source)) {
            return { section: config.audit, rows: config.audit.rows.filter((row) => row.preset === source) };
        }
        return { section, rows: section.rows };
    }

    function showMetricDetails(card) {
        const metric = config[activeSectionKey].metrics[Number(card.dataset.reportMetric)];
        if (!metric) return;
        const { section, rows } = metricComposition(metric.source);
        const layer = document.querySelector('[data-dialog-layer="report-indicator-details"]');
        const dialog = layer?.querySelector('[role="dialog"]');
        const title = dialog?.querySelector('.jemp-dialog__title');
        const description = dialog?.querySelector('.jemp-dialog__description');
        if (title) title.textContent = metric.label;
        if (description) description.textContent = `${metric.value} · ${rows.length} registro(s) demonstrativo(s) relacionados.`;
        renderTable(dialog?.querySelector('.jemp-table-region'), section.columns, rows.slice(0, 10), section.statusVariants);
        openDialog(layer, card.querySelector('[data-dialog-open]') || card);
    }

    function activateReport(sectionKey) {
        if (!config[sectionKey]) return;
        activeSectionKey = sectionKey;
        const section = config[sectionKey];
        updatePresets(section);
        const panel = activePanel();
        const heading = panel?.querySelector('[data-report-heading]');
        const description = panel?.querySelector('[data-report-description]');
        if (heading) heading.textContent = section.heading;
        if (description) description.textContent = section.description;
        updateMetrics(section, panel);
        applyFilters();
    }

    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        applyFilters();
    });
    filterOptions?.addEventListener('change', () => applyFilters());
    searchInput?.addEventListener('input', () => applyFilters());

    tabs?.addEventListener('click', (event) => {
        const tab = event.target.closest('[data-tab-target]');
        if (tab) activateReport(tab.dataset.tabTarget);
    });

    reportPage.addEventListener('click', (event) => {
        const card = event.target.closest('[data-report-metric]');
        if (card) {
            showMetricDetails(card);
            return;
        }
        const pageButton = event.target.closest('[data-report-prev], [data-report-next]');
        if (pageButton) {
            currentPage += pageButton.hasAttribute('data-report-next') ? 1 : -1;
            updatePagination(activePanel(), filteredRows);
        }
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-dialog-open="report-record-details"]');
        if (trigger) {
            const record = JSON.parse(trigger.dataset.reportRecord || '{}');
            fillRecordDialog(record);
            openDialog(document.querySelector('[data-dialog-layer="report-record-details"]'), trigger);
            return;
        }
        if (!activeDialog) return;
        if (event.target === activeDialog || event.target.closest('[data-dialog-dismiss]')) {
            activeDialog.hidden = true;
            document.body.classList.remove('reports-dialog-open');
            lastTrigger?.focus();
            activeDialog = null;
            lastTrigger = null;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && activeDialog) {
            activeDialog.hidden = true;
            document.body.classList.remove('reports-dialog-open');
            lastTrigger?.focus();
            activeDialog = null;
            lastTrigger = null;
        }
    });

    activateReport('overview');
}
