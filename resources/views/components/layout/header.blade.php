@props(['page' => 'home'])

@php
    $pageTitles = [
        'home' => 'Visão geral',
        'products' => 'Produtos',
        'customers' => 'Clientes',
        'invoice-consult' => 'Consulta NF-e',
        'sales' => 'Vendas',
        'entrance' => 'Entradas',
        'exits' => 'Saídas',
        'internal' => 'Internas',
        'reports' => 'Relatórios',
    ];
@endphp

<header class="jemp-header">
    <button class="jemp-header__menu" type="button" data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="Abrir menu">
        <x-layout.icon name="menu" />
    </button>

    <span class="jemp-header__page-title">{{ $pageTitles[$page] ?? 'JEMP' }}</span>

    <details class="jemp-user-menu">
        <summary class="jemp-user-menu__trigger" aria-label="Perfil de {{ auth()->user()->name }}">
            <span class="jemp-user-menu__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}</span>
            <span class="jemp-user-menu__name">{{ auth()->user()->name }}</span>
            <x-layout.icon name="caret-down" />
        </summary>
        <div class="jemp-user-menu__popover">
            <span class="jemp-user-menu__caption">{{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="jemp-user-menu__logout">Sair</button>
            </form>
        </div>
    </details>
</header>
