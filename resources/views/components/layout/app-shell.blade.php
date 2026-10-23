@props(['page' => 'home'])

<div class="jemp-shell" data-layout-shell>
    <x-layout.sidebar :page="$page" />

    <div class="jemp-shell__main">
        <x-layout.header :page="$page" />

        <main class="page jemp-shell__page" id="main-content" tabindex="-1">
            {{ $slot }}
        </main>
    </div>

    <button class="jemp-shell__backdrop" type="button" data-sidebar-close aria-label="Fechar menu"></button>
    <div class="jemp-toast-region" data-toast-region aria-live="polite" aria-relevant="additions"></div>
</div>
