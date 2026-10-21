function initLogin() {
    document.querySelectorAll('.login-form').forEach((form) => {
        const password = form.querySelector('#login-password');
        const toggle = form.querySelector('[data-password-toggle]');

        toggle?.addEventListener('click', () => {
            const showing = password.type === 'password';
            password.type = showing ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(showing));
            toggle.setAttribute('aria-label', showing ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
}

document.addEventListener('DOMContentLoaded', initLogin);
