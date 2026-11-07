<x-layouts.app title="Entrar · JEMP">
    <main class="login-page">
        <section class="login-story" aria-label="Sobre a JEMP">
            <header class="login-story__header">
                <a class="login-brand" href="{{ route('login') }}" aria-label="JEMP, página de login">
                    <span>JEMP</span>
                </a>
                <span class="login-story__version" aria-label="Versão 1.0.0">v1.0.0</span>
            </header>

            <div class="login-story__copy">
                <h1><span>Gestão que</span><span>movimenta</span><span>o seu negócio.</span></h1>
                <p>Estoque, notas fiscais e muito mais,<br class="login-story__desktop-break"> em um só lugar.</p>
            </div>

            <div class="login-story__footer">
                <span>Mais controle.</span>
                <span>Mais eficiência.</span>
                <span>Mais resultados.</span>
            </div>
        </section>

        <section class="login-art" aria-label="JEMP, sistema de gestão">
            <div class="login-art__shape login-art__shape--one" aria-hidden="true"></div>
            <div class="login-art__shape login-art__shape--two" aria-hidden="true"></div>
            <div class="login-art__header" aria-hidden="true">
                <span class="login-art__rule"></span>
                <span class="login-art__dash"></span>
                <span class="login-art__modules">Estoque<br>Notas fiscais<br>Resultados</span>
            </div>
            <div class="login-art__caption" aria-hidden="true">
                <span>Tecnologia</span>
                <span>para uma gestão</span>
                <span class="login-art__caption-muted">mais simples</span>
            </div>
        </section>

        <section class="login-card" aria-labelledby="login-title">
            <a class="login-card__brand" href="{{ route('login') }}" aria-label="JEMP, página de login">
                <span class="login-card__logo" aria-hidden="true">
                    <svg viewBox="0 0 48 48" focusable="false">
                        <path d="M5 14 22 5v36L5 32V14Z" fill="currentColor" />
                        <path d="m26 5 17 9v18l-17 9V5Z" fill="currentColor" opacity=".72" />
                        <path d="m9 17 9 5v4l-9-5v-4Zm0 9 9 5v4l-9-5v-4Zm21-13 8 4v4l-8-4v-4Zm0 9 8 4v4l-8-4v-4Z" fill="white" />
                    </svg>
                </span>
                <span class="login-card__wordmark">JEMP</span>
                <span class="login-card__tagline">Sistema de gerenciamento<br>de estoque e notas fiscais</span>
            </a>

            <h1 class="login-card__accessible-title" id="login-title">Acesse sua conta</h1>

            <form class="login-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="login-field">
                    <label for="login-username">Usuário</label>
                    <div class="login-field__control">
                        <span class="login-field__icon" aria-hidden="true"><x-layout.icon name="user" /></span>
                        <input id="login-username" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="Digite seu usuário" required>
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
                @error('email')
                    <p class="login-form__notice is-submitted" role="alert">{{ $message }}</p>
                @enderror
                @error('password')
                    <p class="login-form__notice is-submitted" role="alert">{{ $message }}</p>
                @enderror
            </form>

            <footer class="login-card__footer">
                <strong>JEMP v1.0.0</strong>
                <span>Todos os direitos reservados.</span>
            </footer>
        </section>
    </main>
</x-layouts.app>
