const region = document.querySelector('[data-toast-region]');
const NOTICE_DURATION = 15_000;

function dismissNotice(notice) {
    if (!notice || notice.dataset.dismissing === 'true') return;
    notice.dataset.dismissing = 'true';
    notice.classList.add('jemp-toast--leaving');
    notice.addEventListener('animationend', () => notice.remove(), { once: true });
    window.setTimeout(() => notice.remove(), 250);
}

document.querySelectorAll('.jemp-notice:not(.jemp-toast)').forEach((notice) => {
    if (notice.closest('[hidden]')) return;
    notice.querySelector('.jemp-notice__close')?.addEventListener('click', () => dismissNotice(notice));
    window.setTimeout(() => dismissNotice(notice), NOTICE_DURATION);
});

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

    close.addEventListener('click', () => dismissNotice(toast));
    toast.append(icon, content, close);
    region.append(toast);
    window.setTimeout(() => dismissNotice(toast), NOTICE_DURATION);
}

document.querySelectorAll('[data-toast-message]').forEach((source) => {
    showFeedback(source.textContent.trim(), source.dataset.toastVariant || 'error');
    source.remove();
});
