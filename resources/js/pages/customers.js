import { showFeedback } from '../components/feedback.js';

document.querySelectorAll('[data-customers-list]').forEach((list) => {
    const queryInput = list.querySelector('[data-customer-search]');
    const paymentFilter = list.querySelector('[data-customer-payment]');
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
            recordId.value = record?.customer_id ?? '';
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
        const paymentTerms = paymentFilter?.value ?? '';
        const filteredRows = rows.filter((row) => row.dataset.search.toLocaleLowerCase('pt-BR').includes(query)
            && (!paymentTerms || row.dataset.payment === paymentTerms));
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
    paymentFilter?.addEventListener('change', () => {
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
            const detailLabels = ['Cliente / Empresa', 'Documento', 'Contato', 'E-mail', 'Condição de pagamento'];
            const detailKeys = ['customer_name', 'customer_document', 'customer_contact', 'customer_email', 'customer_payment_terms'];
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
            detailsDialog.querySelector('.jemp-dialog__title').textContent = `Detalhes de ${customer.customer_name}`;
            detailsLayer.hidden = false;
            document.body.classList.add('customers-dialog-open');
            return;
        }
        if (trigger.matches('[data-customer-delete]') && confirmLayer && confirmDialog && deleteForm) {
            pendingDeleteTrigger = trigger;
            deleteForm.action = row.dataset.deleteUrl;
            const message = confirmDialog.querySelector('.jemp-confirmation__message');
            if (message) {
                message.textContent = `Confirma a exclusão do cliente ${customer.customer_name}? Esta ação não pode ser desfeita.`;
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
<<<<<<< HEAD
        if (event.target.closest('[data-movement-confirm]') && pendingDeleteRow) {
            const deletedName = pendingDeleteRow.cells[0].textContent.trim();
            pendingDeleteRow.remove();
            rows.splice(rows.indexOf(pendingDeleteRow), 1);
            pendingDeleteRow = null;
            filterRows();
            closeLayer(confirmLayer, list.querySelector('[data-customer-open]'));
            pendingDeleteTrigger = null;
            showFeedback(`Cliente ${deletedName} removido da demonstração. A alteração não foi salva no banco.`);
=======
        if (event.target.closest('[data-movement-confirm]')) {
            deleteForm?.requestSubmit();
>>>>>>> feature/ft-database-views
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
<<<<<<< HEAD
    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const values = ['name', 'document', 'city', 'phone', 'email', 'status'].map((key) => String(data.get(key) ?? ''));
        const wasEditing = Boolean(editingRow);
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
        showFeedback(`Cliente ${wasEditing ? 'atualizado' : 'adicionado'} na demonstração. A alteração não foi salva no banco.`);
    });
=======
    list.querySelector('[data-customer-open]')?.addEventListener('click', (event) => setFormMode(null, event.currentTarget));

    if (list.dataset.validationErrors === 'true') {
        const oldRecordId = list.dataset.oldRecordId;
        const row = rows.find((item) => JSON.parse(item.dataset.record).customer_id === Number(oldRecordId));
        setFormMode(row ?? null, row?.querySelector('[data-customer-edit]') ?? list.querySelector('[data-customer-open]'), true);
    }

    renderRows();
>>>>>>> feature/ft-database-views
});
