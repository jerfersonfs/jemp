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
                <svg class="login-card__brand-image" viewBox="544 224 582 480" role="img" aria-labelledby="login-brand-title">
                    <title id="login-brand-title">JEMP</title>
                    <defs>
                        <filter id="login-brand-teal" color-interpolation-filters="sRGB" x="0" y="0" width="1680" height="944" filterUnits="userSpaceOnUse">
                            <feColorMatrix values="0 0 0 0 0.09 0 0 0 0 0.24 0 0 0 0 0.25 0.299 0.587 0.114 0 0" />
                            <feComponentTransfer><feFuncA type="linear" slope="4" intercept="-0.82" /></feComponentTransfer>
                        </filter>
                        <filter id="login-brand-turquoise" color-interpolation-filters="sRGB" x="0" y="0" width="1680" height="944" filterUnits="userSpaceOnUse">
                            <feColorMatrix values="0 0 0 0 0.03 0 0 0 0 0.58 0 0 0 0 0.56 -1 1 0 0 0" />
                            <feComponentTransfer><feFuncA type="linear" slope="3" intercept="-0.8" /></feComponentTransfer>
                        </filter>
                    </defs>
                    <image href="{{ asset('images/logo_jemp_png.png') }}" x="0" y="0" width="1680" height="944" filter="url(#login-brand-teal)" />
                    <image href="{{ asset('images/logo_jemp_png.png') }}" x="0" y="0" width="1680" height="944" filter="url(#login-brand-turquoise)" />
                </svg>
                <span class="login-card__tagline">Sistema de gerenciamento<br>de estoque e notas fiscais</span>
            </a>

            <h1 class="login-card__accessible-title" id="login-title">Acesse sua conta</h1>

            <form class="login-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="login-field">
                    <label for="login-username">Usuário</label>
                    <div class="login-field__control">
                        <span class="login-field__icon" aria-hidden="true"><x-layout.icon name="user" /></span>
                        <input id="login-username" name="email" type="email" value="{{ old('email') }}"
                            autocomplete="username" placeholder="Digite seu usuário" required>
                    </div>
                </div>
                <div class="login-field">
                    <label for="login-password">Senha</label>
                    <div class="login-field__control">
                        <span class="login-field__icon" aria-hidden="true"><x-layout.icon name="lock" /></span>
                        <input id="login-password" name="password" type="password" autocomplete="current-password"
                            placeholder="Digite sua senha" required>
                        <button class="login-field__toggle" type="button" data-password-toggle
                            aria-label="Mostrar senha" aria-pressed="false">
                            <x-layout.icon name="eye" />
                        </button>
                    </div>
                </div>
                <div class="login-form__options"><span></span><button type="button" data-password-help>Esqueceu sua
                        senha?</button></div>
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
