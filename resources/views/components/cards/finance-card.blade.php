@props([
    'title' => 'Total a Receber',
    'value' => 'R$48.500,00',
    'variant' => 'receivable'
])

<article {{ $attributes->class(['finance-card', "finance-card--{$variant}"]) }}>
    <h3 class="finance-card__title">{{ $title }}</h3>
    <div class="finance-card__footer">
        <span class="finance-card__value">{{ $value }}</span>
    </div>
</article>
