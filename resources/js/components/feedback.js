import { startNavigationLoading } from './loading.js';

const region = document.querySelector('[data-toast-region]');
const noticeDuration = 8000;

function enableDismissal(toast) {
    let timer;
    const pause = () => window.clearTimeout(timer);
    const resume = () => {
        pause();
        if (toast.matches('.jemp-notice--success, .jemp-notice--info') && !toast.matches(':hover, :focus-within')) {
            timer = window.setTimeout(() => toast.remove(), noticeDuration);
        }
    };

    toast.querySelector('.jemp-notice__close')?.addEventListener('click', () => {
        pause();
        toast.remove();
    });
    toast.addEventListener('mouseenter', pause);
    toast.addEventListener('mouseleave', resume);
    toast.addEventListener('focusin', pause);
    toast.addEventListener('focusout', () => queueMicrotask(resume));
    resume();
}

export function showFeedback(message, variant = 'success') {
    if (!region || !message) return;
    const templates = region.querySelectorAll('[data-toast-template]');
    const template = [...templates].find((item) => item.dataset.toastTemplate === variant);
    if (!template) return;

    const toast = template.content.firstElementChild.cloneNode(true);
    toast.querySelector('.jemp-notice__message').textContent = message;
    region.append(toast);
    enableDismissal(toast);
}

export function reloadWithFeedback(message) {
    history.replaceState({ ...history.state, jempFeedback: message }, '');
    startNavigationLoading();
    window.location.reload();
}

if (region) {
    // Keep notices in the active dialog's keyboard and accessibility scope.
    const syncModal = () => {
        const nativeModal = document.activeElement?.closest('dialog:modal:not([data-operation-loading])') ?? document.querySelector('dialog:modal:not([data-operation-loading])');
        const modal = nativeModal ?? (region.showPopover && document.querySelector('[aria-modal="true"]:not([hidden], [hidden] *, dialog:not([open]) *)'));
        const parent = modal ?? document.body;
        if (region.parentElement === parent) return;
        parent.append(region);
        if (modal && region.showPopover) {
            region.popover = 'manual';
            region.showPopover();
        } else {
            region.removeAttribute('popover');
        }
    };
    const modalChanges = new MutationObserver((records) => {
        if (records.some(({ target }) => target.matches('dialog, [aria-modal="true"]') || target.querySelector('[aria-modal="true"]'))) {
            syncModal();
        }
    });
    modalChanges.observe(document.body, { subtree: true, attributes: true, attributeFilter: ['open', 'hidden'] });
    syncModal();

    region.querySelectorAll('.jemp-toast').forEach(enableDismissal);
    if (history.state?.jempFeedback) {
        const { jempFeedback, ...state } = history.state;
        history.replaceState(state, '');
        showFeedback(jempFeedback);
    }
}
