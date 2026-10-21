document.querySelectorAll('[data-customers-list]').forEach((list) => {
    const queryInput = list.querySelector('[data-customer-search]');
    const statusFilter = list.querySelector('[data-customer-status]');
    const rows = [...list.querySelectorAll('[data-customer-row]')];
    const emptyRow = list.querySelector('[data-customer-empty]');
    const summary = list.querySelector('[data-customer-summary]');
    const dialog = document.querySelector('[data-customer-dialog]');
    const form = dialog?.querySelector('[data-customer-form]');
    const title = dialog?.querySelector('#customer-dialog-title');
    const editDialog = dialog?.querySelector('[data-customer-edit-dialog]');
    const detailsLayer = document.querySelector('[data-customer-details-layer]');
    const confirmLayer = document.querySelector('[data-customer-confirm-layer]');
    const detailsDialog = detailsLayer?.querySelector('[data-customer-details]');
    const confirmDialog = confirmLayer?.querySelector('[data-customer-confirm-dialog]');
    let editingRow = null;
    let activeTrigger = null;
    let pendingDeleteRow = null;
    let pendingDeleteTrigger = null;

    const filterRows = () => {
        const query = queryInput?.value.trim().toLocaleLowerCase('pt-BR') ?? '';
        const status = statusFilter?.value ?? '';
        let visibleCount = 0;
        rows.forEach((row) => {
            const matches = row.dataset.search.includes(query) && (!status || row.dataset.status === status);
            row.hidden = !matches;
            if (matches) visibleCount++;
        });
        if (emptyRow) emptyRow.hidden = visibleCount > 0;
        if (summary) summary.textContent = visibleCount ? `Mostrando 1–${visibleCount} de ${visibleCount} clientes demonstrativos` : 'Nenhum cliente encontrado';
    };

    const closeDialog = () => {
        if (!dialog) return;
        dialog.hidden = true;
        editDialog?.classList.remove('jemp-dialog--editing');
        form?.classList.remove('jemp-form-dialog--edit');
        form?.classList.add('jemp-form-dialog--create');
        document.body.classList.remove('customers-dialog-open');
        activeTrigger?.focus();
        editingRow = null;
    };

    const openDialog = (trigger, row = null) => {
        if (!dialog || !form) return;
        activeTrigger = trigger;
        editingRow = row;
        form.reset();
        title.textContent = row ? 'Editar Cliente' : 'Adicionar Cliente';
        editDialog?.classList.toggle('jemp-dialog--editing', Boolean(row));
        form.classList.toggle('jemp-form-dialog--edit', Boolean(row));
        form.classList.toggle('jemp-form-dialog--create', !row);
        if (row) {
            [...form.elements].forEach((field) => {
                const values = [...row.cells].map((cell) => cell.textContent.trim());
                const key = { name: 0, document: 1, city: 2, phone: 3, email: 4, status: 5 }[field.name];
                if (key !== undefined) field.value = values[key];
            });
        }
        dialog.hidden = false;
        document.body.classList.add('customers-dialog-open');
        dialog.querySelector('input')?.focus();
    };

    queryInput?.addEventListener('input', filterRows);
    statusFilter?.addEventListener('change', filterRows);
    const closeLayer = (layer, trigger) => {
        if (!layer) return;
        layer.hidden = true;
        document.body.classList.remove('customers-dialog-open');
        trigger?.focus();
    };

    list.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-customer-open], [data-customer-edit], [data-customer-view], [data-customer-delete]');
        if (!trigger) return;
        const row = trigger.closest('[data-customer-row]');
        if (trigger.matches('[data-customer-open], [data-customer-edit]')) {
            openDialog(trigger, row);
            return;
        }

        if (trigger.matches('[data-customer-view]') && detailsLayer && detailsDialog) {
            const customer = [...row.cells].slice(0, 6).map((cell) => cell.textContent.trim());
            const detailLabels = ['Cliente / Empresa', 'CNPJ', 'Cidade', 'Telefone', 'E-mail', 'Status'];
            const grid = detailsDialog.querySelector('.jemp-details-dialog__grid');
            grid.replaceChildren();
            detailLabels.forEach((label, index) => {
                const item = document.createElement('div');
                item.className = 'jemp-details-dialog__item';
                const term = document.createElement('dt');
                term.textContent = label;
                const description = document.createElement('dd');
                description.textContent = customer[index] || '—';
                item.append(term, description);
                grid.append(item);
            });
            detailsDialog.querySelector('.jemp-dialog__title').textContent = `Detalhes de ${customer[0]}`;
            detailsLayer.hidden = false;
            document.body.classList.add('customers-dialog-open');
            activeTrigger = trigger;
            return;
        }

        if (trigger.matches('[data-customer-delete]') && confirmLayer && confirmDialog) {
            pendingDeleteRow = row;
            pendingDeleteTrigger = trigger;
            const message = confirmDialog.querySelector('.jemp-confirmation__message');
            if (message) message.textContent = `Confirma a exclusão do cliente ${row.cells[0].textContent.trim()}? Esta ação não pode ser desfeita.`;
            confirmLayer.hidden = false;
            document.body.classList.add('customers-dialog-open');
        }
    });
    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-customer-close]')) closeDialog();
    });
    detailsLayer?.addEventListener('click', (event) => {
        if (event.target === detailsLayer || event.target.closest('[data-dialog-dismiss]')) closeLayer(detailsLayer, activeTrigger);
    });
    confirmLayer?.addEventListener('click', (event) => {
        if (event.target === confirmLayer || event.target.closest('[data-dialog-dismiss]')) {
            pendingDeleteRow = null;
            closeLayer(confirmLayer, pendingDeleteTrigger);
            pendingDeleteTrigger = null;
            return;
        }
        if (event.target.closest('[data-movement-confirm]') && pendingDeleteRow) {
            pendingDeleteRow.remove();
            rows.splice(rows.indexOf(pendingDeleteRow), 1);
            pendingDeleteRow = null;
            filterRows();
            closeLayer(confirmLayer, list.querySelector('[data-customer-open]'));
            pendingDeleteTrigger = null;
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && dialog && !dialog.hidden) closeDialog();
        if (event.key === 'Escape' && detailsLayer && !detailsLayer.hidden) closeLayer(detailsLayer, activeTrigger);
        if (event.key === 'Escape' && confirmLayer && !confirmLayer.hidden) {
            pendingDeleteRow = null;
            closeLayer(confirmLayer, pendingDeleteTrigger);
            pendingDeleteTrigger = null;
        }
    });
    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const values = ['name', 'document', 'city', 'phone', 'email', 'status'].map((key) => String(data.get(key) ?? ''));
        let row = editingRow;
        if (!row) {
            row = document.createElement('tr');
            row.dataset.customerRow = '';
            row.innerHTML = '<td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
            list.querySelector('[data-customer-rows]').prepend(row);
            rows.unshift(row);
        }
        values.slice(0, 5).forEach((value, index) => { row.cells[index].textContent = value; });
        const statusCell = row.cells[5];
        statusCell.innerHTML = `<span class="jemp-status-badge jemp-status-badge--${values[5] === 'Ativo' ? 'success' : 'danger'}"><span class="jemp-status-badge__dot"></span>${values[5]}</span>`;
        row.cells[6].innerHTML = '<div class="customers-table__actions"><button class="customers-table__action customers-table__action--view" type="button" data-customer-view aria-label="Ver detalhes" title="Ver detalhes"><i class="ph ph-eye" aria-hidden="true"></i></button><button class="customers-table__action customers-table__action--edit" type="button" data-customer-edit aria-label="Editar cliente" title="Editar cliente"><i class="ph ph-pencil-simple-line" aria-hidden="true"></i></button><button class="customers-table__action customers-table__action--delete" type="button" data-customer-delete aria-label="Excluir cliente" title="Excluir cliente"><i class="ph ph-trash" aria-hidden="true"></i></button></div>';
        row.dataset.status = values[5];
        row.dataset.search = values.join(' ').toLocaleLowerCase('pt-BR');
        filterRows();
        closeDialog();
    });
});
