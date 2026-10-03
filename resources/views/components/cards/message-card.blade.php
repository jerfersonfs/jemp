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

<style>
    .message-card {
        display: flex;
        min-height: 76px;
        align-items: center;
        gap: var(--spacing-sm);
        border-radius: 7px;
        padding: var(--spacing-sm) var(--spacing-md);
        font-family: var(--font-family);
    }

    .message-card-filled {
        background: var(--color-surface);
        color: var(--color-secondary);
    }

    .message-card-outlined {
        border: 1px solid var(--color-border);
        background: var(--color-background);
        color: var(--color-heading);
    }

    .message-card__icon {
        display: block;
        width: var(--spacing-xl);
        height: var(--spacing-xl);
        border-radius: 50%;
        background: var(--color-surface);
    }

    .message-card-filled .message-card__icon {
        background: var(--color-primary);
    }

    .message-card__body {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .message-card__value {
        font-size: var(--font-size-body);
        font-weight: 600;
        line-height: 1.15;
    }

    .message-card-filled .message-card__value {
        color: var(--color-primary);
    }

    .message-card__label {
        font-size: var(--font-size-label);
        line-height: 1.3;
    }

    .message-card__trend {
        margin-top: var(--spacing-xs);
        color: var(--color-success);
        font-size: var(--font-size-caption);
    }

    .message-card__trend span {
        margin-left: var(--spacing-xs);
            color: var(--color-caption);
}
</style>
