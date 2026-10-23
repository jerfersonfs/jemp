@props([
    'title',
    'items' => [],
    'description' => null,
])

<section {{ $attributes->class(['notice-list']) }} aria-label="{{ $title }}">
    <header class="notice-list__header">
        <div>
            <h2 class="notice-list__title">{{ $title }}</h2>
            @if ($description)
                <p class="notice-list__description">{{ $description }}</p>
            @endif
        </div>
        <span class="notice-list__count">{{ count($items) }} itens</span>
    </header>

    <ul class="notice-list__items">
        @forelse ($items as $item)
            <li class="notice-list__item">
                <div class="notice-list__item-content">
                    <div class="notice-list__item-heading">
                        <strong>{{ $item['title'] }}</strong>
                        @if (!empty($item['reference']))
                            <span class="notice-list__reference">{{ $item['reference'] }}</span>
                        @endif
                    </div>
                    @if (!empty($item['description']))
                        <p class="notice-list__item-description">{{ $item['description'] }}</p>
                    @endif
                    @if (!empty($item['details']))
                        <div class="notice-list__details">
                            @foreach ($item['details'] as $detail)
                                <span><small>{{ $detail['label'] }}</small>{{ $detail['value'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <x-status.badge :label="$item['status']" :variant="$item['variant'] ?? 'neutral'" />
            </li>
        @empty
            <li class="notice-list__empty">Nenhum aviso no momento.</li>
        @endforelse
    </ul>
</section>
