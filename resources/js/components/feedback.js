const region = document.querySelector('[data-toast-region]');

export function showFeedback(message, variant = 'success') {
    if (!region || !message) return;

    const toast = document.createElement('div');
    toast.className = `jemp-notice jemp-notice--${variant} jemp-toast`;
    toast.setAttribute('role', variant === 'error' ? 'alert' : 'status');

    const icon = document.createElement('span');
    icon.className = 'jemp-notice__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = variant === 'error' ? '!' : variant === 'success' ? '✓' : 'i';

    const content = document.createElement('div');
    content.className = 'jemp-notice__content';
    const text = document.createElement('span');
    text.className = 'jemp-notice__message';
    text.textContent = message;
    content.append(text);

    const close = document.createElement('button');
    close.className = 'jemp-notice__close';
    close.type = 'button';
    close.setAttribute('aria-label', 'Fechar notificação');
    close.textContent = '×';

    let timeout;
    const dismiss = () => {
        clearTimeout(timeout);
        toast.classList.add('jemp-toast--leaving');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
        setTimeout(() => toast.remove(), 250);
    };

    close.addEventListener('click', dismiss);
    toast.append(icon, content, close);
    region.append(toast);
    timeout = setTimeout(dismiss, 5000);
}
