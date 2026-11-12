document.querySelectorAll('[data-customers-list]').forEach((list) => {
    const queryInput = list.querySelector('[data-customer-search]');
    const paymentFilter = list.querySelector('[data-customer-payment]');
    const rows = [...list.querySelectorAll('[data-customer-row]')];
    const emptyRow = list.querySelector('[data-customer-empty]');
    const summary = list.querySelector('[data-customer-summary]');
    const dialog = document.querySelector('[data-customer-dialog]');
    const form = dialog?.querySelector('[data-customer-form]');
    const documentType = form?.querySelector('[data-customer-document-type]');
    const documentInput = form?.querySelector('[data-customer-document]');
    const contactInput = form?.querySelector('[data-customer-contact]');
    const documentDigits = (value) => String(value ?? '').replace(/\D/g, '');
    const formatCustomerContact = (value) => {
        let digits = documentDigits(value);
        if (digits.startsWith('55') && (digits.length === 12 || digits.length === 13)) {
            digits = digits.slice(2);
        }
        digits = digits.slice(0, 11);
        if (!digits) return '';
        if (digits.length <= 2) return digits;

        if (digits.length < 10) {
            const firstPartLength = digits.length > 8 ? 5 : 4;
            if (digits.length <= firstPartLength) return digits;
            return `${digits.slice(0, firstPartLength)}-${digits.slice(firstPartLength)}`;
        }

        const areaCode = digits.slice(0, 2);
        const number = digits.slice(2);
        const firstPartLength = digits.length > 10 ? 5 : 4;
        if (number.length <= firstPartLength) return `(${areaCode}) ${number}`;

        return `(${areaCode}) ${number.slice(0, firstPartLength)}-${number.slice(firstPartLength)}`;
    };
    const syncCustomerContact = (input = contactInput) => {
        if (!input) return;

        const previousValue = input.value;
        const previousCaret = input.selectionStart ?? previousValue.length;
        const digitsBeforeCaret = documentDigits(previousValue.slice(0, previousCaret)).length;
        const formattedValue = formatCustomerContact(previousValue);
        input.value = formattedValue;

        if (document.activeElement !== input || typeof input.setSelectionRange !== 'function') return;

        let nextCaret = 0;
        let digitsCounted = 0;
        while (nextCaret < formattedValue.length && digitsCounted < digitsBeforeCaret) {
            if (/\d/.test(formattedValue[nextCaret])) digitsCounted += 1;
            nextCaret += 1;
        }
        while (nextCaret < formattedValue.length && /\D/.test(formattedValue[nextCaret])) nextCaret += 1;
        input.setSelectionRange(nextCaret, nextCaret);
    };
    const formatCustomerDocument = (value, type) => {
        const digits = documentDigits(value).slice(0, type === 'cnpj' ? 14 : 11);
        if (type === 'cnpj') {
            return digits
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        }
        return digits
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1-$2');
    };
    const syncCustomerDocument = (inferType = false) => {
        if (!documentType || !documentInput) return;
        if (inferType) {
            documentType.value = documentDigits(documentInput.value).length > 11 ? 'cnpj' : 'cpf';
        }
        documentInput.maxLength = documentType.value === 'cnpj' ? 18 : 14;
        documentInput.value = formatCustomerDocument(documentInput.value, documentType.value);
    };
    documentInput?.addEventListener('input', () => syncCustomerDocument());
    documentType?.addEventListener('change', () => syncCustomerDocument());
    contactInput?.addEventListener('input', () => syncCustomerContact());
    rows.forEach((row) => {
        const contactCell = row.querySelector('[data-customer-contact-display]');
        if (contactCell) contactCell.textContent = formatCustomerContact(contactCell.textContent);
    });
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
        syncCustomerDocument(true);
        syncCustomerContact();
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
                description.textContent = detailKeys[index] === 'customer_contact'
                    ? formatCustomerContact(customer.customer_contact) || '—'
                    : customer[detailKeys[index]] || '—';
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
        if (event.target.closest('[data-customer-close]')) {
            closeDialog();
        }
    });
    detailsLayer?.addEventListener('click', (event) => {
        if (event.target.closest('[data-dialog-dismiss]')) {
            closeLayer(detailsLayer, activeTrigger);
        }
    });
    confirmLayer?.addEventListener('click', (event) => {
        if (event.target.closest('[data-dialog-dismiss]')) {
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
        const row = rows.find((item) => JSON.parse(item.dataset.record).customer_id === Number(oldRecordId));
        setFormMode(row ?? null, row?.querySelector('[data-customer-edit]') ?? list.querySelector('[data-customer-open]'), true);
    }

    renderRows();
});
