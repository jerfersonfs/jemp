@props([
    'label' => 'Total customers',
    'value' => '2,420',
    'variant' => 'default',
    'size' => 'regular',
    'href' => '#',
])

<article {{ $attributes->class(['stat-card', "stat-card--{$variant}", "stat-card--{$size}"]) }}>
    <div class="stat-card__content">
        <span class="stat-card__label">{{ $label }}</span>
        <strong class="stat-card__value">{{ $value }}</strong>
    </div>
    <a class="stat-card__link" href="{{ $href }}">Ver dados</a>
</article>

<style>
    .stat-card {
        --stat-background: var(--color-surface);
        --stat-foreground: var(--color-heading);
        --stat-muted: var(--color-caption);
        --stat-border: var(--color-border);

        display: flex;
        min-width: 0;
        min-height: 112px;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;

        border: 1px solid var(--stat-border);
        border-radius: 7px;

        background: var(--stat-background);
        color: var(--stat-foreground);

        font-family: var(--font-family);
    }

    .stat-card--mint {
        --stat-background: var(--color-surface);
        --stat-foreground: var(--color-heading);
        --stat-muted: var(--color-caption);
        --stat-border: var(--color-surface);
    }
    .stat-card--dark {
        --stat-background: var(--color-secondary);
        --stat-foreground: var(--color-surface);
        --stat-muted: var(--color-subheading);
        --stat-border: var(--color-subheading);
    }

    .stat-card__content {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
        padding: var(--spacing-sm) var(--spacing-md);
    }

    .stat-card__label {
        color: var(--stat-muted);
        font-size: var(--font-size-caption);
        line-height: 1.3;
    }

    .stat-card__value {
        font-size: 28px;
        font-weight: 600;
        line-height: 1.15;
        letter-spacing: 0.03em;
    }

    .stat-card__link {
        align-self: stretch;
        border-top: 1px solid color-mix(
            in srgb,
            var(--stat-border),
            transparent 12%
        );

        padding: var(--spacing-sm) var(--spacing-md);

        color: var(--color-info);
        font-size: var(--font-size-caption);
        line-height: 1;

        text-align: right;
        text-decoration: none;
    }

    .stat-card--dark .stat-card__link {
        color: var(--stat-foreground);
    }

    .stat-card__link:hover {
        text-decoration: underline;
    }
    .stat-card--large {
        min-height: 150px;
    }

    .stat-card--large .stat-card__content {
        padding: var(--spacing-md) var(--spacing-lg) var(--spacing-sm);
    }

    .stat-card--large .stat-card__label {
        font-size: var(--font-size-body);
    }

    .stat-card--large .stat-card__value {
        font-size: 32px;
    }

    .stat-card--large .stat-card__link {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-body);
    }

    .stat-card--compact {
        min-height: 64px;
        flex-direction: row;
        align-items: center;
    }

    .stat-card--compact .stat-card__content {
        flex: 1;
        gap: var(--spacing-xs);
        padding: var(--spacing-sm) var(--spacing-md);
    }

    .stat-card--compact .stat-card__label {
        order: 2;
        font-size: 9px;
        text-align: right;
    }

    .stat-card--compact .stat-card__value {
        order: 1;
        font-size: 19px;
    }

    .stat-card--compact .stat-card__link {
        display: flex;
        align-self: stretch;
        align-items: center;

        border-top: 0;
        border-left: 1px solid var(--stat-border);

        padding: var(--spacing-sm);
        font-size: var(--font-size-caption);
    }
</style>
