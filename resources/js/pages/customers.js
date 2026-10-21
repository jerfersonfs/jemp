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
    const deleteForm = confirmLayer?.querySelector('[data-customer-delete-form]');
    const pageSize = 10;
    let currentPage = 1;
    let activeTrigger = null;
    let pendingDeleteTrigger = null;

    const closeDialog = () => {
        if (!dialog) {
            return;
        }
        dialog.hidden = true;
        editDialog?.classList.remove('jemp-dialog--editing');
        form?.classList.remove('jemp-form-dialog--edit');
        form?.classList.add('jemp-form-dialog--create');
        document.body.classList.remove('customers-dialog-open');
        activeTrigger?.focus();
    };

    const setFormMode = (row = null, trigger = null, preserveOldInput = false) => {
        if (!dialog || !form) {
            return;
        }
        activeTrigger = trigger;
        form.reset();
        const record = row ? JSON.parse(row.dataset.record) : null;
        const editing = Boolean(record);
        form.action = editing ? row.dataset.updateUrl : form.dataset.customerStoreUrl;
        let methodInput = form.querySelector('[name="_method"]');
        if (editing) {
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.append(methodInput);
            }
            methodInput.value = 'PATCH';
        } else {
            methodInput?.remove();
        }
        const recordId = form.elements.namedItem('_record_id');
        if (recordId) {
            recordId.value = record?.id ?? '';
        }
        title.textContent = editing ? 'Editar Cliente' : 'Adicionar Cliente';
        editDialog?.classList.toggle('jemp-dialog--editing', editing);
        form.classList.toggle('jemp-form-dialog--edit', editing);
        form.classList.toggle('jemp-form-dialog--create', !editing);
        if (record && !preserveOldInput) {
            for (const [key, value] of Object.entries(record)) {
                const field = form.elements.namedItem(key);
                if (field) {
                    field.value = value ?? '';
                }
            }
        }
        dialog.hidden = false;
        document.body.classList.add('customers-dialog-open');
        dialog.querySelector('input:not([type="hidden"])')?.focus();
    };

    const closeLayer = (layer, trigger) => {
        if (!layer) {
            return;
        }
        layer.hidden = true;
        document.body.classList.remove('customers-dialog-open');
        trigger?.focus();
    };

    const renderRows = () => {
        const query = queryInput?.value.trim().toLocaleLowerCase('pt-BR') ?? '';
        const status = statusFilter?.value ?? '';
        const filteredRows = rows.filter((row) => row.dataset.search.toLocaleLowerCase('pt-BR').includes(query)
            && (!status || row.dataset.status === status));
        const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        filteredRows.forEach((row, index) => {
            row.hidden = index < (currentPage - 1) * pageSize || index >= currentPage * pageSize;
        });
        rows.filter((row) => !filteredRows.includes(row)).forEach((row) => {
            row.hidden = true;
        });
        if (emptyRow) {
            emptyRow.hidden = filteredRows.length > 0;
        }
        if (summary) {
            const first = filteredRows.length ? (currentPage - 1) * pageSize + 1 : 0;
            const last = Math.min(currentPage * pageSize, filteredRows.length);
            summary.textContent = filteredRows.length
                ? `Mostrando ${first}–${last} de ${filteredRows.length} clientes`
                : 'Nenhum cliente encontrado';
        }
        const previous = list.querySelector('[data-customer-previous]');
        const next = list.querySelector('[data-customer-next]');
        const pages = list.querySelector('[data-customer-pages]');
        previous.disabled = currentPage === 1;
        next.disabled = currentPage === totalPages;
        pages.replaceChildren();
        for (let page = 1; page <= totalPages; page += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = page;
            button.dataset.page = page;
            if (page === currentPage) {
                button.classList.add('is-current');
                button.setAttribute('aria-current', 'page');
            }
            pages.append(button);
        }
    };

    queryInput?.addEventListener('input', () => {
        currentPage = 1;
        renderRows();
    });
    statusFilter?.addEventListener('change', () => {
        currentPage = 1;
        renderRows();
    });
    list.querySelector('[data-customer-previous]')?.addEventListener('click', () => {
        currentPage -= 1;
        renderRows();
    });
    list.querySelector('[data-customer-next]')?.addEventListener('click', () => {
        currentPage += 1;
        renderRows();
    });
    list.querySelector('[data-customer-pages]')?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-page]');
        if (button) {
            currentPage = Number(button.dataset.page);
            renderRows();
        }
    });

    list.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-customer-open], [data-customer-edit], [data-customer-view], [data-customer-delete]');
        if (!trigger) {
            return;
        }
        const row = trigger.closest('[data-customer-row]');
        if (trigger.matches('[data-customer-open], [data-customer-edit]')) {
            setFormMode(row, trigger);
            return;
        }
        const customer = JSON.parse(row.dataset.record);
        if (trigger.matches('[data-customer-view]') && detailsLayer && detailsDialog) {
            activeTrigger = trigger;
            const detailLabels = ['Cliente / Empresa', 'CNPJ', 'Cidade', 'Telefone', 'E-mail', 'Status'];
            const detailKeys = ['name', 'document', 'city', 'phone', 'email', 'status'];
            const grid = detailsDialog.querySelector('.jemp-details-dialog__grid');
            grid.replaceChildren();
            detailLabels.forEach((label, index) => {
                const item = document.createElement('div');
                item.className = 'jemp-details-dialog__item';
                const term = document.createElement('dt');
                term.textContent = label;
                const description = document.createElement('dd');
                description.textContent = customer[detailKeys[index]] || '—';
                item.append(term, description);
                grid.append(item);
            });
            detailsDialog.querySelector('.jemp-dialog__title').textContent = `Detalhes de ${customer.name}`;
            detailsLayer.hidden = false;
            document.body.classList.add('customers-dialog-open');
            return;
        }
        if (trigger.matches('[data-customer-delete]') && confirmLayer && confirmDialog && deleteForm) {
            pendingDeleteTrigger = trigger;
            deleteForm.action = row.dataset.deleteUrl;
            const message = confirmDialog.querySelector('.jemp-confirmation__message');
            if (message) {
                message.textContent = `Confirma a exclusão do cliente ${customer.name}? Esta ação não pode ser desfeita.`;
            }
            confirmLayer.hidden = false;
            document.body.classList.add('customers-dialog-open');
        }
    });

    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-customer-close]')) {
            closeDialog();
        }
    });
    detailsLayer?.addEventListener('click', (event) => {
        if (event.target === detailsLayer || event.target.closest('[data-dialog-dismiss]')) {
            closeLayer(detailsLayer, activeTrigger);
        }
    });
    confirmLayer?.addEventListener('click', (event) => {
        if (event.target === confirmLayer || event.target.closest('[data-dialog-dismiss]')) {
            closeLayer(confirmLayer, pendingDeleteTrigger);
            pendingDeleteTrigger = null;
            return;
        }
        if (event.target.closest('[data-movement-confirm]')) {
            deleteForm?.requestSubmit();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && dialog && !dialog.hidden) {
            closeDialog();
        }
        if (event.key === 'Escape' && detailsLayer && !detailsLayer.hidden) {
            closeLayer(detailsLayer, activeTrigger);
        }
        if (event.key === 'Escape' && confirmLayer && !confirmLayer.hidden) {
            closeLayer(confirmLayer, pendingDeleteTrigger);
            pendingDeleteTrigger = null;
        }
    });
    list.querySelector('[data-customer-open]')?.addEventListener('click', (event) => setFormMode(null, event.currentTarget));

    if (list.dataset.validationErrors === 'true') {
        const oldRecordId = list.dataset.oldRecordId;
        const row = rows.find((item) => JSON.parse(item.dataset.record).id === Number(oldRecordId));
        setFormMode(row ?? null, row?.querySelector('[data-customer-edit]') ?? list.querySelector('[data-customer-open]'), true);
    }

    renderRows();
});
