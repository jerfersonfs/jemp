const catalog = document.querySelector('[data-product-catalog]');

if (catalog) {
    const formModal = catalog.querySelector('[data-product-form-modal]');
    const detailsModal = catalog.querySelector('[data-product-details-modal]');
    const deleteModal = catalog.querySelector('[data-product-delete-modal]');
    const form = formModal.querySelector('form');
    const deleteForm = deleteModal.querySelector('[data-product-delete-form]');
    const tableBody = catalog.querySelector('tbody');
    const rows = [...tableBody.querySelectorAll('tr[data-table-row]')];
    const find = (name) => catalog.querySelector(`[data-product-${name}]`);
    const pageSize = 10;
    let currentPage = 1;
    let returnFocus = null;

    const normalize = (value) => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    const formatMeasure = (value) => Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function setFormMethod(method) {
        let methodInput = form.querySelector('[name="_method"]');
        if (method === 'POST') {
            methodInput?.remove();
            return;
        }
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.append(methodInput);
        }
        methodInput.value = method;
    }

    function openForm(product, trigger, preserveOldInput = false) {
        returnFocus = trigger;
        form.reset();
        const editing = Boolean(product);
        form.action = editing ? trigger.closest('tr').dataset.updateUrl : formModal.dataset.productStoreUrl;
        setFormMethod(editing ? 'PATCH' : 'POST');
        let recordId = form.elements.namedItem('_record_id');
        if (!recordId) {
            recordId = document.createElement('input');
            recordId.type = 'hidden';
            recordId.name = '_record_id';
            form.append(recordId);
        }
        recordId.value = product?.id ?? '';
        const title = editing ? 'Editar produto' : 'Novo produto';
        const panel = formModal.querySelector('.jemp-dialog');
        formModal.setAttribute('aria-label', title);
        panel.setAttribute('aria-label', title);
        panel.querySelector('.jemp-dialog__title').textContent = title;
        panel.classList.toggle('jemp-dialog--editing', editing);
        form.classList.toggle('jemp-form-dialog--edit', editing);
        form.classList.toggle('jemp-form-dialog--create', !editing);
        form.querySelector('[type="submit"]').textContent = editing ? 'Salvar alterações' : 'Cadastrar produto';
        if (product && !preserveOldInput) {
            for (const [name, value] of Object.entries(product)) {
                const field = form.elements.namedItem(name);
                if (field) {
                    field.value = value;
                }
            }
        }
        formModal.showModal();
        form.elements.namedItem('code').focus();
    }

    function renderDetails(product, trigger) {
        returnFocus = trigger;
        const title = `Detalhes do produto ${product.code}`;
        detailsModal.setAttribute('aria-label', title);
        detailsModal.querySelector('.jemp-details-dialog').setAttribute('aria-label', title);
        detailsModal.querySelector('.jemp-dialog__title').textContent = title;
        const values = [product.code, product.name, product.category, product.type, `${formatMeasure(product.width)} m`, `${formatMeasure(product.length)} m`, `${product.quantity} un.`, product.location, product.material, product.wood];
        detailsModal.querySelectorAll('dd').forEach((element, index) => {
            element.textContent = values[index] ?? '—';
        });
        const body = detailsModal.querySelector('tbody');
        body.replaceChildren();
        for (const location of ['Barracão 1', 'Barracão 2', 'Barracão 3']) {
            const balance = location === product.location ? Number(product.quantity) : 0;
            const row = document.createElement('tr');
            for (const value of [product.code, `${product.name} ${product.type}`, `${balance} un.`, location]) {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            }
            const statusCell = document.createElement('td');
            const statusTemplate = find(balance > 0 ? 'stock-available' : 'stock-empty');
            statusCell.append(statusTemplate.content.cloneNode(true));
            row.append(statusCell);
            body.append(row);
        }
        detailsModal.showModal();
    }

    function renderRows() {
        const query = normalize(find('search').value.trim());
        const category = find('category').value;
        const type = find('type').value;
        const filtered = rows.filter((row) => {
            const product = JSON.parse(row.dataset.record);
            return normalize(row.dataset.search).includes(query)
                && (!category || product.category === category)
                && (!type || product.type === type);
        });
        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        filtered.forEach((row, index) => {
            row.hidden = index < (currentPage - 1) * pageSize || index >= currentPage * pageSize;
        });
        let emptyRow = tableBody.querySelector('[data-product-empty]');
        if (filtered.length === 0 && rows.length > 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.dataset.productEmpty = '';
                const cell = document.createElement('td');
                cell.colSpan = 9;
                cell.className = 'jemp-table__empty';
                cell.textContent = 'Nenhum produto encontrado.';
                emptyRow.append(cell);
                tableBody.append(emptyRow);
            }
            emptyRow.hidden = false;
        } else if (emptyRow) {
            emptyRow.hidden = true;
        }
        rows.filter((row) => !filtered.includes(row)).forEach((row) => {
            row.hidden = true;
        });
        const first = filtered.length ? (currentPage - 1) * pageSize + 1 : 0;
        const last = Math.min(currentPage * pageSize, filtered.length);
        find('count').textContent = `Mostrando ${first}–${last} de ${filtered.length} ${filtered.length === 1 ? 'produto' : 'produtos'}`;
        find('prev').disabled = currentPage === 1;
        find('next').disabled = currentPage === totalPages;
        const pages = find('pages');
        pages.replaceChildren();
        for (let page = 1; page <= totalPages; page += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = page;
            button.dataset.page = page;
            button.setAttribute('aria-label', `Página ${page}`);
            if (page === currentPage) {
                button.setAttribute('aria-current', 'page');
            }
            pages.append(button);
        }
    }

    catalog.querySelector('[data-product-create]').addEventListener('click', (event) => {
        openForm(null, event.currentTarget);
    });
    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-row-action]');
        if (!button) {
            return;
        }
        const row = button.closest('tr');
        const product = JSON.parse(row.dataset.record);
        if (button.dataset.rowAction === 'edit') {
            openForm(product, button);
        } else if (button.dataset.rowAction === 'view') {
            renderDetails(product, button);
        } else if (button.dataset.rowAction === 'delete') {
            returnFocus = button;
            deleteForm.action = row.dataset.deleteUrl;
            deleteModal.querySelector('.jemp-confirmation__message').textContent = `Tem certeza de que deseja excluir o produto ${product.code} — ${product.name} ${product.type}? Essa ação não poderá ser desfeita.`;
            deleteModal.showModal();
            deleteModal.querySelector('.jemp-confirmation__button--cancel').focus();
        }
    });

    catalog.querySelectorAll('.jemp-modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target.closest('[data-dialog-dismiss], .jemp-dialog__close, .jemp-confirmation__button--cancel')) {
                modal.close();
            }
            if (event.target.closest('[data-movement-confirm]')) {
                deleteForm.requestSubmit();
            }
        });
        modal.addEventListener('close', () => {
            returnFocus?.focus();
        });
    });

    for (const name of ['search', 'category', 'type']) {
        find(name).addEventListener(name === 'search' ? 'input' : 'change', () => {
            currentPage = 1;
            renderRows();
        });
    }
    find('filters').addEventListener('submit', (event) => event.preventDefault());
    find('prev').addEventListener('click', () => {
        currentPage -= 1;
        renderRows();
    });
    find('next').addEventListener('click', () => {
        currentPage += 1;
        renderRows();
    });
    find('pages').addEventListener('click', (event) => {
        const button = event.target.closest('[data-page]');
        if (button) {
            currentPage = Number(button.dataset.page);
            renderRows();
        }
    });
    find('notice').querySelector('.jemp-notice__close')?.addEventListener('click', () => {
        find('notice').hidden = true;
    });

    if (catalog.dataset.validationErrors === 'true') {
        const oldRecordId = catalog.dataset.oldRecordId;
        const row = rows.find((item) => JSON.parse(item.dataset.record).id === Number(oldRecordId));
        const trigger = row?.querySelector('[data-row-action="edit"]') ?? catalog.querySelector('[data-product-create]');
        openForm(row ? JSON.parse(row.dataset.record) : null, trigger, true);
    }

    renderRows();
}
