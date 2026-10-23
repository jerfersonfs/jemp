<x-layouts.app title="Entrar · JEMP">
    <main class="login-page">
        <section class="login-story" aria-label="Sobre a JEMP">
            <a class="login-brand" href="{{ route('home') }}" aria-label="JEMP, página inicial">
                <span class="login-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" focusable="false">
                        <path d="M5 14 22 5v36L5 32V14Z" fill="currentColor" />
                        <path d="m26 5 17 9v18l-17 9V5Z" fill="currentColor" opacity=".72" />
                        <path d="m9 17 9 5v4l-9-5v-4Zm0 9 9 5v4l-9-5v-4Zm21-13 8 4v4l-8-4v-4Zm0 9 8 4v4l-8-4v-4Z" fill="var(--color-secondary)" />
                    </svg>
                </span>
                <span>JEMP</span>
            </a>
            <div class="login-story__copy">
                <span class="login-story__eyebrow">Gestão de estoque e notas fiscais</span>
                <h1>Gestão que movimenta o seu negócio.</h1>
                <p>Estoque, notas fiscais e muito mais, em um só lugar.</p>
            </div>
            <div class="login-story__footer"><span>Mais controle.</span><span>Mais eficiência.</span><span>Mais resultados.</span></div>
        </section>

        <section class="login-art" aria-hidden="true">
            <div class="login-art__shape login-art__shape--one"></div>
            <div class="login-art__shape login-art__shape--two"></div>
            <div class="login-art__caption"><span>JEMP · 1.0.0</span><span>Sistema de gerenciamento e estoque de notas fiscais</span></div>
        </section>

        <section class="login-card" aria-labelledby="login-title">
            <a class="login-card__brand" href="{{ route('home') }}" aria-label="JEMP, página inicial">
                <span class="login-card__logo" aria-hidden="true">
                    <svg viewBox="0 0 48 48" focusable="false">
                        <path d="M5 14 22 5v36L5 32V14Z" fill="currentColor" />
                        <path d="m26 5 17 9v18l-17 9V5Z" fill="currentColor" opacity=".72" />
                        <path d="m9 17 9 5v4l-9-5v-4Zm0 9 9 5v4l-9-5v-4Zm21-13 8 4v4l-8-4v-4Zm0 9 8 4v4l-8-4v-4Z" fill="white" />
                    </svg>
                </span>
                <span class="login-card__wordmark">JEMP</span>
                <span class="login-card__tagline">Sistema de gerenciamento<br>e estoque de notas fiscais</span>
            </a>

            <h2 id="login-title">Acesse sua conta</h2>
            <p class="login-card__intro">Entre para acompanhar a sua operação.</p>

            <form class="login-form" data-demo-login>
                <div class="login-field">
                    <label for="login-username">Usuário</label>
                    <div class="login-field__control">
                        <span class="login-field__icon" aria-hidden="true"><x-layout.icon name="user" /></span>
                        <input id="login-username" name="username" type="text" autocomplete="username" placeholder="Digite seu usuário" required>
                    </div>
                </div>
                <div class="login-field">
                    <label for="login-password">Senha</label>
                    <div class="login-field__control">
                        <span class="login-field__icon" aria-hidden="true"><x-layout.icon name="lock" /></span>
                        <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Digite sua senha" required>
                        <button class="login-field__toggle" type="button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                            <x-layout.icon name="eye" />
                        </button>
                    </div>
                </div>
                <div class="login-form__options"><span></span><button type="button" data-password-help>Esqueceu sua senha?</button></div>
                <x-buttons.button type="submit" class="login-form__submit">Entrar</x-buttons.button>
                <p class="login-form__notice" data-login-notice role="status">Ambiente demonstrativo · sem autenticação ativa</p>
            </form>
        </section>
    </main>
</x-layouts.app>
