const page = document.querySelector('[data-warehouse-page]');

if (page) {
    page.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-dialog-open]');
        if (trigger) {
            document.getElementById(trigger.dataset.dialogOpen)?.showModal();
        } else if (event.target.closest('[data-dialog-dismiss]')) {
            event.target.closest('dialog')?.close();
        }
    });

    page.querySelector('dialog[data-reopen]')?.showModal();
}
