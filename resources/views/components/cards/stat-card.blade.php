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
        --stat-background: #fff;
        --stat-foreground: #292929;
        --stat-muted: #77858a;
        --stat-border: #edf0f1;
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
        font-family: inherit;
    }

    .stat-card--mint { --stat-background: #edfffc; --stat-border: #edfffc; }
    .stat-card--dark {
        --stat-background: #385d5c;
        --stat-foreground: #f5ffff;
        --stat-muted: #e0eeee;
        --stat-border: #98aeac;
    }

    .stat-card__content { display: flex; flex-direction: column; gap: 4px; padding: 14px 16px 10px; }
    .stat-card__label { color: var(--stat-muted); font-size: 12px; line-height: 1.3; }
    .stat-card__value { font-size: 28px; font-weight: 600; line-height: 1.15; letter-spacing: -.03em; }
    .stat-card__link {
        align-self: stretch;
        border-top: 1px solid color-mix(in srgb, var(--stat-border), transparent 12%);
        padding: 9px 14px;
        color: #008f83;
        font-size: 11px;
        line-height: 1;
        text-align: right;
        text-decoration: none;
    }
    .stat-card--dark .stat-card__link { color: var(--stat-foreground); }
    .stat-card__link:hover { text-decoration: underline; }
    .stat-card--large { min-height: 150px; }
    .stat-card--large .stat-card__content { padding: 18px 20px 14px; }
    .stat-card--large .stat-card__label { font-size: 13px; }
    .stat-card--large .stat-card__value { font-size: 38px; }
    .stat-card--large .stat-card__link { padding: 12px 18px; }
    .stat-card--compact { min-height: 64px; flex-direction: row; align-items: center; }
    .stat-card--compact .stat-card__content { gap: 2px; padding: 9px 12px; }
    .stat-card--compact .stat-card__label { order: 2; font-size: 9px; text-align: right; }
    .stat-card--compact .stat-card__value { order: 1; font-size: 19px; }
    .stat-card--compact .stat-card__link { align-self: stretch; display: flex; align-items: end; border-top: 0; padding: 9px 10px; font-size: 9px; }
    @media (max-width: 480px) {
        .stat-card--large { min-height: 128px; }
        .stat-card--large .stat-card__value { font-size: 32px; }
    }
</style>
