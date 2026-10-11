function initLayoutShell() {
    document.querySelectorAll('[data-layout-shell]').forEach((shell) => {
        const openButton = shell.querySelector('[data-sidebar-open]');
        const closeButton = shell.querySelector('[data-sidebar-close]');
        const sidebar = shell.querySelector('#app-sidebar');

        if (!openButton || !closeButton || !sidebar) return;

        const closeSidebar = () => {
            shell.classList.remove('is-sidebar-open');
            openButton.setAttribute('aria-expanded', 'false');
            openButton.focus();
        };

        openButton.addEventListener('click', () => {
            shell.classList.add('is-sidebar-open');
            openButton.setAttribute('aria-expanded', 'true');
            sidebar.querySelector('a, summary')?.focus();
        });

        closeButton.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && shell.classList.contains('is-sidebar-open')) {
                closeSidebar();
            }
        });

        sidebar.addEventListener('click', (event) => {
            if (event.target.closest('a') && window.matchMedia('(max-width: 900px)').matches) {
                shell.classList.remove('is-sidebar-open');
                openButton.setAttribute('aria-expanded', 'false');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initLayoutShell);
