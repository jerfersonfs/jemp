document.querySelectorAll('[data-select-control]').forEach((select) => {
    const setOpen = (open) => select.setAttribute('data-open', String(open));

    select.addEventListener('focus', () => setOpen(true));
    select.addEventListener('click', () => setOpen(true));
    select.addEventListener('change', () => setOpen(false));
    select.addEventListener('blur', () => setOpen(false));
    select.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' || event.key === 'Enter') setOpen(false);
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') setOpen(true);
    });
});
