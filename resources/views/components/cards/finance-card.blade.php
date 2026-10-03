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

<style>
    .finance-card {
        --finance-accent: var(--color-primary);

        display: flex;
        min-height: 61px;
        flex-direction: column;
        justify-content: center;
        gap: var(--spacing-xs);

        border: 1px solid var(--finance-accent);
        border-radius: 12px;

        background: var(--color-surface);
        padding: var(--spacing-sm) var(--spacing-md);

        font-family: var(--font-family);
    }

    .finance-card--overdue {
        --finance-accent: var(--color-danger);
    }

    .finance-card--pending {
        --finance-accent: var(--color-warning);
    }

    .finance-card__title {
        margin: 0;

        color: var(--finance-accent);
        font-size: var(--font-size-h2);
        font-weight: 600;
        line-height: 1.2;
    }

    .finance-card__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--spacing-sm);
    }

    .finance-card__value {
        color: var(--color-heading);
        font-size: var(--font-size-label);
        line-height: 1.2;
    }
</style>
