@props([
    'value' => '220',
    'label' => 'Messages',
    'variant' => 'filled',
    'trend' => null,
    'trendLabel' => 'Since last month',
])

<article {{ $attributes->class(['message-card', "message-card--{$variant}"]) }}>
    <span class="message-card__icon" aria-hidden="true"></span>
    <div class="message-card__body">
        <strong class="message-card__value">{{ $value }}</strong>
        <span class="message-card__label">{{ $label }}</span>
        @if ($trend !== null)
            <span class="message-card__trend">↗ {{ $trend }} <span>{{ $trendLabel }}</span></span>
        @endif
    </div>
</article>
