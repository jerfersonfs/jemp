const formatCurrency = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

document.querySelectorAll('[data-sales-form]').forEach((form) => {
    const itemList = form.querySelector('[data-sales-items]');
    const subtotalOutput = form.querySelector('[data-sales-subtotal]');
    const discountOutput = form.querySelector('[data-sales-discount]');
    const totalOutput = form.querySelector('[data-sales-total]');
    const feedback = form.parentElement.querySelector('[data-sales-feedback]');
    const dialog = document.querySelector('[data-sales-dialog]');
    const dialogForm = dialog?.querySelector('[data-sales-dialog-form]');
    const dialogProduct = dialog?.querySelector('[data-sales-dialog-product]');
    const addDialog = document.querySelector('[data-sales-add-dialog]');
    const addForm = addDialog?.querySelector('[data-sales-add-form]');
    const productSelect = addForm?.querySelector('[data-sales-product]');
    const lotInput = addForm?.querySelector('[data-sales-lot]');
    const priceInput = addForm?.querySelector('[data-sales-price]');
    const confirmLayer = document.querySelector('[data-sales-confirm-layer]');
    const confirmDialog = confirmLayer?.querySelector('[data-sales-confirm-dialog]');
    let activeRow = null;
    let activeTrigger = null;
    let addTrigger = null;
    let pendingDeleteRow = null;
    let pendingDeleteTrigger = null;
    const catalog = {
        'produto-a': { name: 'Produto A', lot: 'LT0001', price: 100 },
        'produto-b': { name: 'Produto B', lot: 'LT0002', price: 150 },
        'produto-c': { name: 'Produto C', lot: 'LT0003', price: 80 },
        'produto-d': { name: 'Produto D', lot: 'LT0004', price: 45 },
        'produto-e': { name: 'Produto E', lot: 'LT0005', price: 30 },
    };

    const updateTotals = () => {
        const rows = [...(itemList?.querySelectorAll('[data-sale-item]') ?? [])];
        const subtotal = rows.reduce((sum, row) => sum + Number(row.dataset.quantity) * Number(row.dataset.price), 0);
        const discount = rows.reduce((sum, row) => sum + Number(row.dataset.discount), 0);
        if (subtotalOutput) subtotalOutput.textContent = formatCurrency(subtotal);
        if (discountOutput) discountOutput.textContent = formatCurrency(discount);
        if (totalOutput) totalOutput.textContent = formatCurrency(subtotal - discount);
    };

    form.addEventListener('click', (event) => {
        const editButton = event.target.closest('[data-sales-edit]');
        if (editButton && dialog && dialogForm) {
            activeRow = editButton.closest('[data-sale-item]');
            activeTrigger = editButton;
            const cells = activeRow.cells;
            dialogProduct.textContent = cells[1].textContent;
            dialogForm.elements.quantity.value = activeRow.dataset.quantity;
            dialogForm.elements.price.value = activeRow.dataset.price;
            dialogForm.elements.discount.value = activeRow.dataset.discount;
            dialog.hidden = false;
            document.body.classList.add('sales-dialog-open');
            dialogForm.elements.quantity.focus();
        }

        const removeButton = event.target.closest('[data-sales-remove]');
        if (removeButton) {
            pendingDeleteRow = removeButton.closest('[data-sale-item]');
            pendingDeleteTrigger = removeButton;
            const productName = pendingDeleteRow?.cells[1].textContent.trim() ?? 'produto';
            const message = confirmDialog?.querySelector('.jemp-confirmation__message');
            if (message) message.textContent = `Confirma a remoção de ${productName} desta venda?`;
            if (confirmLayer) {
                confirmLayer.hidden = false;
                document.body.classList.add('sales-dialog-open');
            }
        }

        if (event.target.closest('[data-sales-add]')) {
            if (addDialog && addForm) {
                addTrigger = event.target.closest('[data-sales-add]');
                addForm.reset();
                if (lotInput) lotInput.value = '';
                if (priceInput) priceInput.value = '';
                addDialog.hidden = false;
                document.body.classList.add('sales-dialog-open');
                productSelect?.focus();
            }
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        if (feedback) feedback.hidden = false;
    });
    form.addEventListener('reset', () => {
        if (feedback) feedback.hidden = true;
    });

    const closeDialog = () => {
        if (!dialog) return;
        dialog.hidden = true;
        document.body.classList.remove('sales-dialog-open');
        activeTrigger?.focus();
        activeRow = null;
    };

    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-sales-dialog-close]')) closeDialog();
    });
    const closeAddDialog = () => {
        if (!addDialog) return;
        addDialog.hidden = true;
        document.body.classList.remove('sales-dialog-open');
        addTrigger?.focus();
        addTrigger = null;
    };
    addDialog?.addEventListener('click', (event) => {
        if (event.target === addDialog || event.target.closest('[data-sales-add-close]')) closeAddDialog();
    });
    productSelect?.addEventListener('change', () => {
        const product = catalog[productSelect.value];
        if (lotInput) lotInput.value = product?.lot ?? '';
        if (priceInput) priceInput.value = product ? String(product.price) : '';
    });
    addForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!addForm.reportValidity()) return;
        const product = catalog[productSelect.value];
        if (!product || !itemList) return;
        const nextNumber = Math.max(0, ...[...itemList.querySelectorAll('[data-sale-item]')].map((row) => Number(row.cells[0].textContent) || 0)) + 1;
        const quantity = Number(addForm.elements.quantity.value);
        const price = Number(addForm.elements.price.value);
        const discount = Number(addForm.elements.discount.value);
        const row = document.createElement('tr');
        row.dataset.saleItem = '';
        row.dataset.quantity = String(quantity);
        row.dataset.price = String(price);
        row.dataset.discount = String(discount);
        row.innerHTML = `<td>${String(nextNumber).padStart(6, '0')}</td><td>${product.name}</td><td>${product.lot}</td><td>${quantity}</td><td>${formatCurrency(price)}</td><td>${formatCurrency(discount)}</td><td data-item-total>${formatCurrency(quantity * price - discount)}</td><td><div class="sales-table__actions"><button class="sales-table__edit" type="button" data-sales-edit aria-label="Editar ${product.name}"><i class="ph ph-pencil-simple-line" aria-hidden="true"></i></button><button class="sales-table__remove" type="button" data-sales-remove aria-label="Remover ${product.name}"><i class="ph ph-trash" aria-hidden="true"></i></button></div></td>`;
        itemList.append(row);
        updateTotals();
        closeAddDialog();
    });
    const closeConfirm = (returnFocus = pendingDeleteTrigger) => {
        if (!confirmLayer) return;
        confirmLayer.hidden = true;
        document.body.classList.remove('sales-dialog-open');
        returnFocus?.focus();
        pendingDeleteRow = null;
        pendingDeleteTrigger = null;
    };
    confirmLayer?.addEventListener('click', (event) => {
        if (event.target === confirmLayer || event.target.closest('[data-dialog-dismiss]')) {
            closeConfirm();
            return;
        }
        if (event.target.closest('[data-movement-confirm]') && pendingDeleteRow) {
            const returnFocus = form.querySelector('[data-sales-add]');
            pendingDeleteRow.remove();
            updateTotals();
            closeConfirm(returnFocus);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && dialog && !dialog.hidden) closeDialog();
        if (event.key === 'Escape' && addDialog && !addDialog.hidden) closeAddDialog();
        if (event.key === 'Escape' && confirmLayer && !confirmLayer.hidden) closeConfirm();
    });
    dialogForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!activeRow || !dialogForm.reportValidity()) return;
        const quantity = Number(dialogForm.elements.quantity.value);
        const price = Number(dialogForm.elements.price.value);
        const discount = Number(dialogForm.elements.discount.value);
        activeRow.dataset.quantity = String(quantity);
        activeRow.dataset.price = String(price);
        activeRow.dataset.discount = String(discount);
        activeRow.cells[3].textContent = String(quantity);
        activeRow.cells[4].textContent = formatCurrency(price);
        activeRow.cells[5].textContent = formatCurrency(discount);
        activeRow.querySelector('[data-item-total]').textContent = formatCurrency(quantity * price - discount);
        updateTotals();
        closeDialog();
    });
});
