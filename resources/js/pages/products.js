const catalog = document.querySelector('[data-product-catalog]');

if (catalog) {
    // O protótipo mantém as alterações em memória até a página ser recarregada.
    let products = JSON.parse(catalog.dataset.products);
    let nextId = Math.max(0, ...products.map((product) => product.id)) + 1;
    let selectedId = null;
    let currentPage = 1;
    let returnFocus = null;
    let noticeTimeout;
    const pageSize = 10;
    const find = (name) => catalog.querySelector(`[data-product-${name}]`);
    const formModal = find('form-modal');
    const detailsModal = find('details-modal');
    const deleteModal = find('delete-modal');
    const form = formModal.querySelector('form');
    const tableBody = catalog.querySelector('tbody');
    const actionTemplate = tableBody.querySelector('.jemp-table__actions').cloneNode(true);
    const normalize = (value) => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    const formatMeasure = (value) => Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const tableValues = (product) => [product.code, product.name, product.category, product.type, `${formatMeasure(product.width)} × ${formatMeasure(product.length)} m`, product.location, product.material, product.wood];
    const warehouses = ['Barracão 1', 'Barracão 2', 'Barracão 3'];
    const inventoryFor = (product) => warehouses.map((location) => ({
        location,
        balance: product.inventory?.find((stock) => stock.location === location)?.balance
            ?? (product.location === location ? product.quantity : 0),
    }));

    function renderInventory(product) {
        const details = [product.code, product.name, product.category, product.type, `${formatMeasure(product.width)} m`, `${formatMeasure(product.length)} m`, `${product.quantity} un.`, product.location, product.material, product.wood];
        detailsModal.querySelectorAll('dd').forEach((element, index) => { element.textContent = details[index]; });
        const body = detailsModal.querySelector('tbody');
        body.replaceChildren();
        for (const stock of inventoryFor(product)) {
            const row = document.createElement('tr');
            for (const value of [product.code, `${product.name} ${product.type}`, `${stock.balance} un.`, stock.location]) {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            }
            const status = document.createElement('td');
            status.append(find(stock.balance > 0 ? 'stock-available' : 'stock-empty').content.cloneNode(true));
            row.append(status);
            body.append(row);
        }
    }

    function notify(message) {
        clearTimeout(noticeTimeout);
        find('notice').querySelector('.jemp-notice__message').textContent = message;
        find('notice').hidden = false;
        noticeTimeout = setTimeout(() => { find('notice').hidden = true; }, 5000);
    }

    function renderProducts() {
        const search = normalize(find('search').value.trim());
        const filtered = products.filter((product) => normalize(`${product.code} ${product.name} ${product.type} ${product.location}`).includes(search)
            && (!find('category').value || product.category === find('category').value)
            && (!find('type').value || product.type === find('type').value));
        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        currentPage = Math.max(1, Math.min(currentPage, totalPages));
        const offset = (currentPage - 1) * pageSize;
        tableBody.replaceChildren();
        for (const product of filtered.slice(offset, offset + pageSize)) {
            const row = document.createElement('tr');
            row.dataset.tableRow = '';
            for (const value of tableValues(product)) {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            }
            const actionsCell = document.createElement('td');
            const actions = actionTemplate.cloneNode(true);
            actions.querySelectorAll('button').forEach((button) => {
                button.dataset.recordId = product.id;
                button.setAttribute('aria-label', `${button.title} ${product.code}`);
            });
            actionsCell.append(actions);
            row.append(actionsCell);
            tableBody.append(row);
        }
        if (!filtered.length) {
            const cell = document.createElement('td');
            cell.colSpan = 9;
            cell.className = 'jemp-table__empty';
            cell.textContent = 'Nenhum produto encontrado.';
            const row = document.createElement('tr');
            row.append(cell);
            tableBody.append(row);
        }
        find('count').textContent = `Mostrando ${filtered.length ? offset + 1 : 0}–${Math.min(offset + pageSize, filtered.length)} de ${filtered.length} ${filtered.length === 1 ? 'produto' : 'produtos'}`;
        find('prev').disabled = currentPage === 1;
        find('next').disabled = currentPage === totalPages;
        find('pages').replaceChildren();
        for (let page = 1; page <= totalPages; page++) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = page;
            button.dataset.page = page;
            button.setAttribute('aria-label', `Página ${page}`);
            if (page === currentPage) { button.setAttribute('aria-current', 'page'); }
            find('pages').append(button);
        }
    }

    function openModal(modal, trigger) {
        returnFocus = trigger;
        modal.showModal();
    }

    function openForm(product, trigger) {
        selectedId = product?.id ?? null;
        form.reset();
        form.elements.namedItem('code').setCustomValidity('');
        const editing = Boolean(product);
        const title = editing ? 'Editar produto' : 'Novo produto';
        formModal.setAttribute('aria-label', title);
        const panel = formModal.querySelector('.jemp-dialog');
        panel.classList.toggle('jemp-dialog--editing', editing);
        panel.setAttribute('aria-label', title);
        panel.querySelector('.jemp-dialog__title').textContent = title;
        form.classList.toggle('jemp-form-dialog--edit', editing);
        form.classList.toggle('jemp-form-dialog--create', !editing);
        form.querySelector('[type="submit"]').textContent = editing ? 'Salvar alterações' : 'Cadastrar produto';
        if (product) {
            for (const [name, value] of Object.entries(product)) {
                const field = form.elements.namedItem(name);
                if (field) { field.value = value; }
            }
        } else {
            let nextCode = 1;
            while (products.some((item) => item.code === String(nextCode).padStart(3, '0'))) { nextCode++; }
            form.elements.namedItem('code').value = String(nextCode).padStart(3, '0');
        }
        openModal(formModal, trigger);
        form.elements.namedItem('code').focus();
    }

    find('create').addEventListener('click', (event) => openForm(null, event.currentTarget));
    find('filters').addEventListener('submit', (event) => { event.preventDefault(); });
    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-row-action]');
        if (!button) { return; }
        const product = products.find((item) => item.id === Number(button.dataset.recordId));
        if (!product) { return; }
        selectedId = product.id;
        if (button.dataset.rowAction === 'edit') { openForm(product, button); }
        if (button.dataset.rowAction === 'view') {
            const title = `Detalhes do produto ${product.code}`;
            detailsModal.setAttribute('aria-label', title);
            detailsModal.querySelector('.jemp-details-dialog').setAttribute('aria-label', title);
            detailsModal.querySelector('.jemp-dialog__title').textContent = title;
            renderInventory(product);
            openModal(detailsModal, button);
        }
        if (button.dataset.rowAction === 'delete') {
            deleteModal.querySelector('.jemp-confirmation__message').textContent = `Tem certeza de que deseja excluir o produto ${product.code} — ${product.name} ${product.type}? Essa ação não poderá ser desfeita.`;
            openModal(deleteModal, button);
            deleteModal.querySelector('.jemp-confirmation__button--cancel').focus();
        }
    });

    catalog.querySelectorAll('.jemp-modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target.closest('[data-dialog-dismiss], .jemp-dialog__close, .jemp-confirmation__button--cancel')) { modal.close(); }
        });
        modal.addEventListener('close', () => {
            const replacement = tableBody.querySelector(`[data-record-id="${selectedId}"][data-row-action="edit"]`);
            if (returnFocus?.isConnected) { returnFocus.focus(); }
            else { (replacement ?? find('create')).focus(); }
        });
    });
    form.addEventListener('input', () => { form.elements.namedItem('code').setCustomValidity(''); });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        for (const name of ['code', 'name', 'material', 'wood']) {
            const field = form.elements.namedItem(name);
            field.value = field.value.trim();
        }
        const code = form.elements.namedItem('code');
        code.setCustomValidity(products.some((product) => normalize(product.code) === normalize(code.value) && product.id !== selectedId) ? 'Informe um código único para o produto.' : '');
        if (!form.reportValidity()) { return; }
        const values = Object.fromEntries(new FormData(form));
        const editing = selectedId !== null;
        const existingProduct = products.find((item) => item.id === selectedId);
        const product = {
            id: selectedId ?? nextId++, code: values.code, name: values.name, category: values.category, type: values.type,
            width: Number(values.width), length: Number(values.length), quantity: Number(values.quantity),
            location: values.location, material: values.material, wood: values.wood,
        };
        product.inventory = inventoryFor(existingProduct ?? product).map((stock) => ({
            ...stock,
            balance: stock.location === product.location ? product.quantity : stock.balance,
        }));
        if (editing) { products = products.map((item) => item.id === selectedId ? product : item); }
        else { products.unshift(product); currentPage = 1; }
        renderProducts();
        formModal.close();
        notify(editing ? 'Produto atualizado com sucesso.' : 'Produto cadastrado com sucesso.');
    });
    deleteModal.querySelector('.jemp-confirmation__button--confirm').addEventListener('click', () => {
        products = products.filter((product) => product.id !== selectedId);
        renderProducts();
        deleteModal.close();
        notify('Produto excluído com sucesso.');
    });
    for (const name of ['search', 'category', 'type']) {
        find(name).addEventListener(name === 'search' ? 'input' : 'change', () => { currentPage = 1; renderProducts(); });
    }
    find('prev').addEventListener('click', () => { currentPage--; renderProducts(); });
    find('next').addEventListener('click', () => { currentPage++; renderProducts(); });
    find('pages').addEventListener('click', (event) => {
        const button = event.target.closest('[data-page]');
        if (button) { currentPage = Number(button.dataset.page); renderProducts(); }
    });
    find('notice').querySelector('.jemp-notice__close').addEventListener('click', () => { find('notice').hidden = true; });
    renderProducts();
}
