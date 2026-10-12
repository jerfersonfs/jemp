@props(['page' => 'home'])

@php
    $sections = [
        [
            ['page' => 'products', 'route' => 'products.index', 'label' => 'Produtos', 'icon' => 'cube'],
            ['page' => 'customers', 'route' => 'customers.index', 'label' => 'Clientes', 'icon' => 'users-three'],
        ],
        [
            ['page' => 'invoice-consult', 'route' => 'invoice-consult.index', 'label' => 'Consulta NF-e', 'icon' => 'note-pencil'],
            ['page' => 'sales', 'route' => 'sales.index', 'label' => 'Vendas', 'icon' => 'handshake'],
        ],
        [
            ['page' => 'entrance', 'route' => 'entrance.index', 'label' => 'Entradas', 'icon' => 'tray-arrow-down'],
            ['page' => 'exits', 'route' => 'exits.index', 'label' => 'Saídas', 'icon' => 'cube'],
            ['page' => 'internal', 'route' => 'internal.index', 'label' => 'Internas', 'icon' => 'arrows-left-right'],
        ],
        [
            ['page' => 'reports', 'route' => 'reports.index', 'label' => 'Relatórios', 'icon' => 'clipboard-text'],
        ],
    ];
@endphp

<aside class="jemp-sidebar" id="app-sidebar" aria-label="Navegação principal">
    <div class="jemp-sidebar__brand-row">
        <a class="jemp-sidebar__brand" href="{{ route('home') }}" aria-label="JEMP, página inicial" title="Página inicial">
            <img src="{{ asset('images/logo-simbolo.jpg') }}" alt="" width="44" height="44">
            <span class="sr-only">JEMP</span>
        </a>
        <button class="jemp-sidebar__collapse" type="button" data-sidebar-collapse aria-controls="app-sidebar" aria-expanded="true" aria-label="Recolher menu lateral" title="Recolher menu lateral">
            <x-layout.icon name="sidebar-simple" />
        </button>
    </div>

    <nav class="jemp-nav" aria-label="Menu principal">
        <a class="jemp-nav__link {{ $page === 'home' ? 'is-active' : '' }}" href="{{ route('home') }}" title="Home" @if ($page === 'home') aria-current="page" @endif>
            <x-layout.icon name="house" />
            <span>Home</span>
        </a>

        @foreach ($sections as $section)
            <div class="jemp-nav__divider" role="separator"></div>
            @foreach ($section as $item)
                <a class="jemp-nav__link {{ $page === $item['page'] ? 'is-active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @if ($page === $item['page']) aria-current="page" @endif>
                    <x-layout.icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="jemp-sidebar__footer">
        <x-layout.icon name="question" />
        <span>Central de ajuda</span>
    </div>
</aside>
