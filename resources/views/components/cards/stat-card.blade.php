@props([
    'label' => 'Total customers',
    'value' => '2,420',
    'variant' => 'default',
    'size' => 'regular',
    'href' => '#',
    'detailsTarget' => null,
])

<article {{ $attributes->class(['stat-card', "stat-card--{$variant}", "stat-card--{$size}"]) }}>
    <div class="stat-card__content">
        <span class="stat-card__label">{{ $label }}</span>
        <strong class="stat-card__value">{{ $value }}</strong>
    </div>
    @if ($detailsTarget)
        <button class="stat-card__link" type="button" data-dialog-open="{{ $detailsTarget }}">Ver dados</button>
    @else
        <a class="stat-card__link" href="{{ $href }}">Ver dados</a>
    @endif
</article>
