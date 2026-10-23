function initializeTabs(root) {
    const tabButtons = [...root.querySelectorAll('[role="tab"][data-tab-target]')];
    const panels = [...root.querySelectorAll('[data-tabs-panels] [data-tab-panel]')];

    function activate(button, moveFocus = false) {
        const selectedId = button.dataset.tabTarget;

        tabButtons.forEach((tab) => {
            const selected = tab === button;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            if (selected && moveFocus) tab.focus();
        });

        panels.forEach((panel) => {
            const selected = panel.dataset.tabPanel === selectedId;
            panel.classList.add('jemp-tabset__panel');
            panel.hidden = !selected;
            panel.id = `${root.dataset.tabs}-panel-${panel.dataset.tabPanel}`;
            panel.setAttribute('role', 'tabpanel');
            panel.tabIndex = 0;
            panel.setAttribute('aria-labelledby', `${root.dataset.tabs}-tab-${panel.dataset.tabPanel}`);
        });
    }

    tabButtons.forEach((button, index) => {
        button.addEventListener('click', () => activate(button));
        button.addEventListener('keydown', (event) => {
            let nextIndex = null;
            if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabButtons.length;
            if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabButtons.length) % tabButtons.length;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = tabButtons.length - 1;
            if (nextIndex === null) return;

            event.preventDefault();
            const nextButton = tabButtons[nextIndex];
            if (!nextButton.disabled) activate(nextButton, true);
        });
    });

    const activeButton = tabButtons.find((button) => button.getAttribute('aria-selected') === 'true') ?? tabButtons[0];
    if (activeButton) activate(activeButton);
}

document.querySelectorAll('[data-tabs]').forEach(initializeTabs);
