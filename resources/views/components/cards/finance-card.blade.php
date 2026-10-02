@props([
    'title' => 'Total a Receber',
    'value' => 'R$48.500,00',
    'variant' => 'receivable',
    'indicator' => '↗',
])

<article {{ $attributes->class(['finance-card', "finance-card--{$variant}"]) }}>
    <h3 class="finance-card__title">{{ $title }}</h3>
    <div class="finance-card__footer">
        <span class="finance-card__value">{{ $value }}</span>
        @if ($indicator !== '')
            <span class="finance-card__indicator" aria-hidden="true">{{ $indicator }}</span>
        @endif
    </div>
</article>

<style>
    .finance-card {
        --finance-accent: var(--color-primary);
        display: flex;
        min-height: 61px;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
        border: 1px solid var(--finance-accent);
        border-radius: 12px;
        background: #edfffc;
        padding: 9px 12px;
        font-family: inherit;
    }
    .finance-card--overdue { --finance-accent: #ea3232; }
    .finance-card--pending { --finance-accent: #e99419; }
    .finance-card__title { margin: 0; color: var(--finance-accent); font-size: 16px; font-weight: 600; line-height: 1.2; }
    .finance-card__footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .finance-card__value { color: #183b3a; font-size: 13px; line-height: 1.2; }
    .finance-card__indicator { color: var(--finance-accent); font-size: 19px; line-height: 1; }
</style>
