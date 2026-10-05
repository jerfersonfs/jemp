document.querySelectorAll('[data-popover]').forEach((popover) => {
    const trigger = popover.querySelector('[data-popover-trigger]');
    const panel = popover.querySelector('[data-popover-panel]');
    if (!trigger || !panel) return;

    function closePopover({ restoreFocus = false } = {}) {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        if (restoreFocus) trigger.focus();
    }

    trigger.addEventListener('click', () => {
        const opening = panel.hidden;
        panel.hidden = !opening;
        trigger.setAttribute('aria-expanded', String(opening));
        if (opening) panel.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([data-popover-close])')?.focus();
    });

    popover.querySelectorAll('[data-popover-close]').forEach((button) => {
        button.addEventListener('click', () => closePopover({ restoreFocus: true }));
    });

    document.addEventListener('click', (event) => {
        if (!popover.contains(event.target)) closePopover();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            event.preventDefault();
            closePopover({ restoreFocus: true });
        }
    });
});
