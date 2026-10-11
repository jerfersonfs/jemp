@props(['page' => 'home'])

@php
    $productPages = ['catalogo', 'produto', 'inventario'];
@endphp

<aside class="jemp-sidebar" id="app-sidebar" aria-label="Navegação principal">
    <a class="jemp-sidebar__brand" href="{{ route('home') }}" aria-label="JEMP, página inicial">
        <svg aria-hidden="true" viewBox="0 0 48 48" focusable="false">
            <path d="M7 14.5 22.2 6v34.5L7 32V14.5Z" fill="#048b80" />
            <path d="M25.8 6 41 14.5V40l-15.2-8.5V6Z" fill="#eefffd" />
            <path d="M10.5 16.5 19 21v4l-8.5-4.5v-4Zm0 9L19 30v4l-8.5-4.5v-4Zm18-11.5 9 5v4l-9-5v-4Zm0 9 9 5v4l-9-5v-4Z" fill="#1b4041" />
            <path d="M22.2 6 25.8 6v34.5l-3.6 2V6Z" fill="#fff" />
        </svg>
        <span class="sr-only">JEMP</span>
    </a>

    <nav class="jemp-nav" aria-label="Menu principal">
        <a class="jemp-nav__link {{ $page === 'home' ? 'is-active' : '' }}" href="{{ route('home') }}" @if ($page === 'home') aria-current="page" @endif>
            <x-layout.icon name="house" />
            <span>Home</span>
        </a>

        <div class="jemp-nav__divider" role="separator"></div>

        <details class="jemp-nav__group" @if (in_array($page, $productPages, true)) open @endif>
            <summary class="jemp-nav__link {{ in_array($page, $productPages, true) ? 'is-current' : '' }}">
                <x-layout.icon name="cube" />
                <span>Produtos</span>
                <x-layout.icon name="caret-down" class="jemp-nav__chevron" />
            </summary>
            <div class="jemp-nav__subnav">
                <a class="jemp-nav__sublink {{ $page === 'catalogo' ? 'is-active' : '' }}" href="{{ route('catalogo') }}" @if ($page === 'catalogo') aria-current="page" @endif>Catálogo</a>
                <a class="jemp-nav__sublink {{ $page === 'inventario' ? 'is-active' : '' }}" href="{{ route('inventario') }}" @if ($page === 'inventario') aria-current="page" @endif>Inventário</a>
                <a class="jemp-nav__sublink {{ $page === 'produto' ? 'is-active' : '' }}" href="{{ route('produtos.create') }}" @if ($page === 'produto') aria-current="page" @endif>Novo produto</a>
            </div>
        </details>

        <a class="jemp-nav__link {{ $page === 'saidas' ? 'is-active' : '' }}" href="{{ route('saidas') }}" @if ($page === 'saidas') aria-current="page" @endif>
            <x-layout.icon name="receipt" />
            <span>Saídas e NF-e</span>
        </a>
    </nav>

    <div class="jemp-sidebar__footer">
        <x-layout.icon name="question" />
        <span>Central de ajuda</span>
    </div>
</aside>
