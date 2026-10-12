function initLayoutShell() {
    document.querySelectorAll('[data-layout-shell]').forEach((shell) => {
        const openButton = shell.querySelector('[data-sidebar-open]');
        const closeButton = shell.querySelector('[data-sidebar-close]');
        const collapseButton = shell.querySelector('[data-sidebar-collapse]');
        const sidebar = shell.querySelector('#app-sidebar');

        if (!openButton || !closeButton || !collapseButton || !sidebar) return;

        let collapsedPreference = false;
        try {
            collapsedPreference = window.localStorage.getItem('jemp-sidebar-collapsed') === 'true';
        } catch (error) {
            // Keep the default expanded layout when storage is unavailable.
        }
        shell.classList.toggle('is-sidebar-collapsed', collapsedPreference && !window.matchMedia('(max-width: 900px)').matches);
        document.documentElement.classList.remove('jemp-sidebar-precollapsed');

        const updateCollapseButton = () => {
            const expanded = !shell.classList.contains('is-sidebar-collapsed');
            collapseButton.setAttribute('aria-expanded', String(expanded));
            collapseButton.setAttribute('aria-label', expanded ? 'Recolher menu lateral' : 'Expandir menu lateral');
            collapseButton.setAttribute('title', expanded ? 'Recolher menu lateral' : 'Expandir menu lateral');
        };
        updateCollapseButton();

        collapseButton.addEventListener('click', () => {
            const collapsed = shell.classList.toggle('is-sidebar-collapsed');
            try {
                window.localStorage.setItem('jemp-sidebar-collapsed', String(collapsed));
            } catch (error) {
                // The current page remains usable even when storage is unavailable.
            }
            updateCollapseButton();
        });

        const closeSidebar = () => {
            shell.classList.remove('is-sidebar-open');
            openButton.setAttribute('aria-expanded', 'false');
            openButton.focus();
        };

        openButton.addEventListener('click', () => {
            shell.classList.add('is-sidebar-open');
            openButton.setAttribute('aria-expanded', 'true');
            sidebar.querySelector('a')?.focus();
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
