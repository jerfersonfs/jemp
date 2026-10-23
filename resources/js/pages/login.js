function initDemoLogin() {
    document.querySelectorAll('[data-demo-login]').forEach((form) => {
        const password = form.querySelector('#login-password');
        const toggle = form.querySelector('[data-password-toggle]');
        const help = form.querySelector('[data-password-help]');
        const notice = form.querySelector('[data-login-notice]');

        toggle?.addEventListener('click', () => {
            const showing = password.type === 'password';
            password.type = showing ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(showing));
            toggle.setAttribute('aria-label', showing ? 'Ocultar senha' : 'Mostrar senha');
        });

        help?.addEventListener('click', () => {
            notice.textContent = 'A recuperação de senha ainda não está habilitada neste protótipo.';
            notice.classList.add('is-submitted');
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            notice.textContent = 'A autenticação ainda não está habilitada neste protótipo.';
            notice.classList.add('is-submitted');
        });
    });
}

document.addEventListener('DOMContentLoaded', initDemoLogin);
