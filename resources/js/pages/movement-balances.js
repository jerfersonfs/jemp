import { showFeedback } from '../components/feedback.js';

export function initializeMovementBalances(form) {
    const container = form?.querySelector('[data-movement-balances]');
    if (!container) return { refresh() {}, clear() {} };

    const fields = [...container.querySelectorAll('[data-movement-balance]')];
    const status = container.querySelector('[data-movement-balance-status]');
    const warehouseNames = { origin: 'origin_warehouse_id', destination: 'dest_warehouse_id' };
    const format = new Intl.NumberFormat('pt-BR');
    let pending = null;

    function clear() {
        pending?.abort();
        pending = null;
        fields.forEach((field) => { field.value = '—'; });
        status.textContent = 'Selecione o produto e o armazém para consultar.';
    }

    async function refresh() {
        clear();
        const productId = form.elements.namedItem('product_id')?.value;
        const condition = form.elements.namedItem('product_condition')?.value;
        const selections = fields.map((field) => ({
            field, warehouseId: form.elements.namedItem(warehouseNames[field.dataset.movementBalance])?.value,
        })).filter((selection) => selection.warehouseId);
        if (!productId || !condition || !selections.length) return;

        const request = new AbortController();
        pending = request;
        const url = new URL(container.dataset.balanceUrl, window.location.origin);
        url.searchParams.set('product_id', productId);
        url.searchParams.set('product_condition', condition);
        [...new Set(selections.map((selection) => selection.warehouseId))].forEach((warehouseId) => {
            url.searchParams.append('warehouse_ids[]', warehouseId);
        });
        selections.forEach(({ field }) => { field.value = 'Consultando…'; });
        status.textContent = 'Consultando saldos atuais…';

        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store', signal: request.signal });
            const result = await response.json().catch(() => ({}));
            if (pending !== request) return;
            if (!response.ok) {
                if (response.status === 401 || response.status === 419) throw new Error('Sua sessão expirou. Recarregue a página e entre novamente.');
                throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Não foi possível consultar o saldo do produto.');
            }
            if (String(result.product_id) !== productId || result.product_condition !== condition || !Array.isArray(result.balances)) {
                throw new Error('Não foi possível confirmar o saldo do produto selecionado.');
            }
            const values = selections.map(({ field, warehouseId }) => {
                const balance = result.balances.find((item) => String(item.warehouse_id) === warehouseId)?.balance;
                if (!Number.isInteger(balance) || balance < 0) throw new Error('O saldo recebido não é válido. Atualize a consulta.');
                return { field, balance };
            });
            values.forEach(({ field, balance }) => { field.value = `${format.format(balance)} un.`; });
            status.textContent = 'Saldos atualizados.';
        } catch (error) {
            if (request.signal.aborted || pending !== request) return;
            selections.forEach(({ field }) => { field.value = 'Indisponível'; });
            status.textContent = 'Falha na consulta. Use “Atualizar saldos” para tentar novamente.';
            showFeedback(error.message || 'Não foi possível consultar o saldo do produto.', 'error');
        } finally {
            if (pending === request) pending = null;
        }
    }

    ['product_id', 'product_condition', 'origin_warehouse_id', 'dest_warehouse_id'].forEach((name) => {
        form.elements.namedItem(name)?.addEventListener('change', refresh);
    });
    container.querySelector('[data-movement-balances-refresh]')?.addEventListener('click', refresh);

    return { refresh, clear };
}
