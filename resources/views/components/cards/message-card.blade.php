@props([
    'value' => '220',
    'label' => 'Messages',
    'variant' => 'filled',
    'trend' => null
])

<article {{ $attributes->class(['message-card', "message-card--{$variant}"]) }}>
    <span class="message-card__icon" aria-hidden="true"></span>
    <div class="message-card__body">
        <strong class="message-card__value">{{ $value }}</strong>
        <span class="message-card__label">{{ $label }}</span>
    </div>
</article>
