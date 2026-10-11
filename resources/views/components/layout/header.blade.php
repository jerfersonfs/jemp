@props(['page' => 'home'])

@php
    $pageTitles = [
        'home' => 'Visão geral',
        'catalogo' => 'Catálogo',
        'produto' => 'Novo produto',
        'inventario' => 'Inventário',
        'saidas' => 'Saídas e NF-e',
    ];
@endphp

<header class="jemp-header">
    <button class="jemp-header__menu" type="button" data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="Abrir menu">
        <x-layout.icon name="menu" />
    </button>

    <span class="jemp-header__page-title">{{ $pageTitles[$page] ?? 'JEMP' }}</span>

    <details class="jemp-user-menu">
        <summary class="jemp-user-menu__trigger" aria-label="Perfil do administrador">
            <span class="jemp-user-menu__avatar" aria-hidden="true">MP</span>
            <span class="jemp-user-menu__name">Administrador</span>
            <x-layout.icon name="caret-down" />
        </summary>
        <div class="jemp-user-menu__popover">
            <span class="jemp-user-menu__caption">Maria Paula</span>
            <a href="{{ route('login') }}">Acessar perfil</a>
        </div>
    </details>
</header>
