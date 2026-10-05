const openButtons = document.querySelectorAll('[data-dialog-open]');
let activeLayer = null;
let activeTrigger = null;

function closeDialog() {
    if (!activeLayer) return;
    activeLayer.hidden = true;
    document.body.classList.remove('preview-dialog-open');
    activeTrigger?.focus();
    activeLayer = null;
    activeTrigger = null;
}

function openDialog(layer, trigger) {
    if (activeLayer) closeDialog();
    activeLayer = layer;
    activeTrigger = trigger;
    layer.hidden = false;
    document.body.classList.add('preview-dialog-open');
    const focusTarget = layer.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([data-dialog-dismiss]), a[href], [tabindex]:not([tabindex="-1"])');
    (focusTarget ?? layer.querySelector('[role="dialog"], [role="alertdialog"]'))?.focus();
}

openButtons.forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const layer = document.querySelector(`[data-dialog-layer="${CSS.escape(trigger.dataset.dialogOpen)}"]`);
        if (layer) openDialog(layer, trigger);
    });
});

document.querySelectorAll('[data-dialog-layer]').forEach((layer) => {
    layer.addEventListener('click', (event) => {
        if (event.target === layer || event.target.closest('[data-dialog-dismiss]')) closeDialog();
        if (event.target.closest('.jemp-dialog__close, .jemp-confirmation__button--cancel')) closeDialog();
        if (event.target.closest('.jemp-confirmation__button--confirm, .jemp-dialog__footer .jemp-dialog__button--primary')) closeDialog();
    });

    layer.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            closeDialog();
        });
    });
});

document.addEventListener('keydown', (event) => {
    if (!activeLayer) return;
    if (event.key === 'Escape') {
        event.preventDefault();
        closeDialog();
        return;
    }
    if (event.key !== 'Tab') return;

    const focusable = [...activeLayer.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])')]
        .filter((element) => element.offsetParent !== null && !element.hasAttribute('data-dialog-dismiss'));
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

document.querySelectorAll('.jemp-notice__close').forEach((button) => {
    button.addEventListener('click', () => button.closest('.jemp-notice')?.remove());
});

document.querySelectorAll('[data-preview-filter]').forEach((form) => {
    form.addEventListener('submit', (event) => event.preventDefault());
});
