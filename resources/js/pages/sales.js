import { showFeedback } from '../components/feedback.js';

const formatCurrency = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

document.querySelectorAll('[data-sales-form]').forEach((form) => {
    const itemList = form.querySelector('[data-sales-items]');
    const subtotalOutput = form.querySelector('[data-sales-subtotal]');
    const discountOutput = form.querySelector('[data-sales-discount]');
    const totalOutput = form.querySelector('[data-sales-total]');
    const feedback = form.querySelector('[data-sales-feedback]');
    const dialog = document.querySelector('[data-sales-dialog]');
    const dialogForm = dialog?.querySelector('[data-sales-dialog-form]');
    const addDialog = document.querySelector('[data-sales-add-dialog]');
    const addForm = addDialog?.querySelector('[data-sales-add-form]');
    const productSelect = addForm?.querySelector('[data-sales-product]');
    const confirmLayer = document.querySelector('[data-sales-confirm-layer]');
    const confirmDialog = confirmLayer?.querySelector('[data-sales-confirm-dialog]');
    let activeRow = null;
    let activeTrigger = null;
    let addTrigger = null;
    let pendingDeleteRow = null;
    let pendingDeleteTrigger = null;

    const rows = () => [...(itemList?.querySelectorAll('[data-sale-item]') ?? [])];
    const reindex = () => rows().forEach((row, index) => {
        row.querySelectorAll('[data-sale-input]').forEach((input) => {
            input.name = `items[${index}][${input.dataset.saleInput}]`;
        });
    });
    const setRowValue = (row, name, value) => {
        const input = row.querySelector(`[data-sale-input="${name}"]`);
        if (input) input.value = String(value);
    };
    const updateTotals = () => {
        const subtotal = rows().reduce((sum, row) => sum + Number(row.dataset.quantity) * Number(row.dataset.price), 0);
        const discount = rows().reduce((sum, row) => sum + Number(row.dataset.discount), 0);
        if (subtotalOutput) subtotalOutput.textContent = formatCurrency(subtotal);
        if (discountOutput) discountOutput.textContent = formatCurrency(discount);
        if (totalOutput) totalOutput.textContent = formatCurrency(subtotal - discount);
    };
    const closeDialog = (layer, trigger) => {
        if (!layer) return;
        layer.hidden = true;
        document.body.classList.remove('sales-dialog-open');
        trigger?.focus();
    };
    const addHiddenInput = (cell, name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.dataset.saleInput = name;
        input.value = String(value);
        cell.append(input);
    };
    const updateRow = (row, quantity, price, discount) => {
        row.dataset.quantity = String(quantity);
        row.dataset.price = String(price);
        row.dataset.discount = String(discount);
        row.cells[3].textContent = String(quantity);
        row.cells[4].textContent = formatCurrency(price);
        row.cells[5].textContent = formatCurrency(discount);
        row.querySelector('[data-item-total]').textContent = formatCurrency(quantity * price - discount);
        setRowValue(row, 'quantity', quantity);
        setRowValue(row, 'unit_price', price);
        setRowValue(row, 'discount', discount);
        updateTotals();
    };

    form.addEventListener('click', (event) => {
        const editButton = event.target.closest('[data-sales-edit]');
        if (editButton && dialog && dialogForm) {
            activeRow = editButton.closest('[data-sale-item]');
            activeTrigger = editButton;
            dialog.querySelector('[data-sales-dialog-product]').textContent = activeRow.cells[1].textContent;
            dialogForm.elements.quantity.value = activeRow.dataset.quantity;
            dialogForm.elements.unit_price.value = activeRow.dataset.price;
            dialogForm.elements.discount.value = activeRow.dataset.discount;
            dialog.hidden = false;
            document.body.classList.add('sales-dialog-open');
            dialogForm.elements.quantity.focus();
        }

        const removeButton = event.target.closest('[data-sales-remove]');
        if (removeButton) {
            pendingDeleteRow = removeButton.closest('[data-sale-item]');
            pendingDeleteTrigger = removeButton;
            const message = confirmDialog?.querySelector('.jemp-confirmation__message');
            if (message) message.textContent = `Confirma a remoção de ${pendingDeleteRow?.cells[1].textContent.trim() ?? 'produto'} desta venda?`;
            if (confirmLayer) {
                confirmLayer.hidden = false;
                document.body.classList.add('sales-dialog-open');
            }
        }

        const addButton = event.target.closest('[data-sales-add]');
        if (addButton && addDialog && addForm) {
            addTrigger = addButton;
            addForm.reset();
            addDialog.hidden = false;
            document.body.classList.add('sales-dialog-open');
            productSelect?.focus();
        }

        if (event.target.closest('[data-sales-reset]')) {
            itemList?.replaceChildren();
            form.reset();
            updateTotals();
            if (feedback) feedback.hidden = true;
        }
    });

    form.addEventListener('submit', (event) => {
<<<<<<< HEAD
        event.preventDefault();
        if (!form.reportValidity()) return;
        if (feedback) feedback.hidden = false;
        showFeedback('Venda validada na demonstração. Nenhum dado foi salvo no banco.', 'info');
=======
        if (!rows().length) {
            event.preventDefault();
            if (feedback) {
                feedback.hidden = false;
                feedback.textContent = 'Adicione pelo menos um produto à venda.';
            }
        }
>>>>>>> feature/ft-database-views
    });

    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-sales-dialog-close]')) closeDialog(dialog, activeTrigger);
    });
    addDialog?.addEventListener('click', (event) => {
        if (event.target === addDialog || event.target.closest('[data-sales-add-close]')) closeDialog(addDialog, addTrigger);
    });
    addForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!addForm.reportValidity() || !itemList) return;
        const selectedOption = productSelect?.selectedOptions[0];
        if (!selectedOption?.value) return;
        const productId = selectedOption.value;
        const productCode = selectedOption.dataset.code ?? '';
        const productName = selectedOption.dataset.name ?? selectedOption.textContent;
        const quantity = Number(addForm.elements.quantity.value);
        const price = Number(addForm.elements.unit_price.value);
        const discount = Number(addForm.elements.discount.value);
        const duplicate = rows().find((row) => row.dataset.productId === productId);
        if (duplicate) {
            updateRow(duplicate, Number(duplicate.dataset.quantity) + quantity, price, Number(duplicate.dataset.discount) + discount);
        } else {
            const row = document.createElement('tr');
            row.dataset.saleItem = '';
            row.dataset.productId = productId;
            row.dataset.quantity = String(quantity);
            row.dataset.price = String(price);
            row.dataset.discount = String(discount);
            for (const value of [productCode, productName, productCode, String(quantity), formatCurrency(price), formatCurrency(discount), formatCurrency(quantity * price - discount)]) {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            }
            row.cells[6].dataset.itemTotal = '';
            const actions = document.createElement('td');
            const actionGroup = document.createElement('div');
            actionGroup.className = 'sales-table__actions';
            const editButton = document.createElement('button');
            editButton.className = 'sales-table__edit';
            editButton.type = 'button';
            editButton.dataset.salesEdit = '';
            editButton.setAttribute('aria-label', `Editar ${productName}`);
            editButton.textContent = 'Editar';
            const removeButton = document.createElement('button');
            removeButton.className = 'sales-table__remove';
            removeButton.type = 'button';
            removeButton.dataset.salesRemove = '';
            removeButton.setAttribute('aria-label', `Remover ${productName}`);
            removeButton.textContent = 'Remover';
            actionGroup.append(editButton, removeButton);
            actions.append(actionGroup);
            addHiddenInput(actions, 'product_id', productId);
            addHiddenInput(actions, 'quantity', quantity);
            addHiddenInput(actions, 'unit_price', price);
            addHiddenInput(actions, 'discount', discount);
            row.append(actions);
            itemList.append(row);
        }
        reindex();
        updateTotals();
<<<<<<< HEAD
        closeAddDialog();
        showFeedback('Item adicionado à venda demonstrativa. A alteração não foi salva no banco.');
=======
        closeDialog(addDialog, addTrigger);
>>>>>>> feature/ft-database-views
    });

    dialogForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!activeRow || !dialogForm.reportValidity()) return;
        const quantity = Number(dialogForm.elements.quantity.value);
        const price = Number(dialogForm.elements.unit_price.value);
        const discount = Number(dialogForm.elements.discount.value);
        if (discount > quantity * price) {
            dialogForm.elements.discount.setCustomValidity('O desconto não pode superar o valor do item.');
            dialogForm.reportValidity();
            dialogForm.elements.discount.setCustomValidity('');
            return;
        }
        updateRow(activeRow, quantity, price, discount);
        closeDialog(dialog, activeTrigger);
        activeRow = null;
        activeTrigger = null;
    });

    const closeConfirm = () => {
        closeDialog(confirmLayer, pendingDeleteTrigger);
        pendingDeleteRow = null;
        pendingDeleteTrigger = null;
    };
    confirmLayer?.addEventListener('click', (event) => {
        if (event.target === confirmLayer || event.target.closest('[data-dialog-dismiss]')) {
            closeConfirm();
            return;
        }
        if (event.target.closest('[data-movement-confirm]') && pendingDeleteRow) {
            pendingDeleteRow.remove();
            reindex();
            updateTotals();
<<<<<<< HEAD
            closeConfirm(returnFocus);
            showFeedback('Item removido da venda demonstrativa. A alteração não foi salva no banco.');
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
        showFeedback('Item atualizado na venda demonstrativa. A alteração não foi salva no banco.');
=======
            closeConfirm();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (dialog && !dialog.hidden) closeDialog(dialog, activeTrigger);
        if (addDialog && !addDialog.hidden) closeDialog(addDialog, addTrigger);
        if (confirmLayer && !confirmLayer.hidden) closeConfirm();
>>>>>>> feature/ft-database-views
    });
    reindex();
    updateTotals();
});
