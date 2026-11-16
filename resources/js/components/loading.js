const indicator = document.querySelector('[data-operation-loading]');
const pending = new Set();
let showTimer;
let finishNavigation;

const loadingDialog = indicator instanceof HTMLDialogElement ? indicator : null;
loadingDialog?.addEventListener('cancel', (event) => event.preventDefault());

function setLoading(visible) {
    if (loadingDialog) {
        if (visible && !loadingDialog.open) loadingDialog.showModal();
        if (!visible && loadingDialog.open) loadingDialog.close();
    } else if (indicator) {
        indicator.hidden = !visible;
    }
}

function beginLoading() {
    const operation = Symbol();
    pending.add(operation);
    if (pending.size === 1 && indicator) {
        showTimer = window.setTimeout(() => setLoading(true), 250);
    }
    return () => {
        if (!pending.delete(operation) || pending.size) return;
        window.clearTimeout(showTimer);
        if (indicator) {
            requestAnimationFrame(() => { if (!pending.size) setLoading(false); });
        }
    };
}

export async function requestJson(url, options) {
    const finish = beginLoading();
    try {
        const response = await fetch(url, options).catch((error) => {
            if (error.name === 'AbortError') throw error;
            throw new Error('Não foi possível obter uma resposta do sistema. Confira a conexão e atualize a página para verificar o resultado.', { cause: error });
        });
        const data = response.status === 204 ? null : await response.json().catch(() => {
            if (response.ok) throw new Error('O sistema devolveu uma resposta inesperada. Atualize a página para conferir o resultado.');
            return {};
        });
        return { response, data };
    } finally {
        finish();
    }
}

export function startNavigationLoading() {
    const finish = beginLoading();
    finishNavigation?.();
    finishNavigation = finish;
    return finish;
}

if (window.navigation) {
    window.navigation.addEventListener('navigate', (event) => {
        if (event.defaultPrevented || event.destination.sameDocument || event.downloadRequest != null) return;
        if (new URL(event.destination.url, location.href).origin !== location.origin) return;
        const finish = startNavigationLoading();
        event.signal.addEventListener('abort', finish, { once: true });
    });
} else {
    window.addEventListener('submit', (event) => {
        const form = event.target;
        const target = event.submitter?.getAttribute('formtarget') || form.target;
        const method = event.submitter?.getAttribute('formmethod') || form.method;
        if (!event.defaultPrevented && method !== 'dialog' && (!target || target === '_self')) startNavigationLoading();
    });
    window.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.hasAttribute('download') || (link.target && link.target !== '_self') || link.origin !== location.origin) return;
        if (link.href.includes('#') && link.pathname === location.pathname && link.search === location.search) return;
        startNavigationLoading();
    });
}

window.addEventListener('pagehide', () => {
    window.clearTimeout(showTimer);
    pending.clear();
    finishNavigation = null;
    setLoading(false);
});
