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
        gap: 12px;
        border-radius: 7px;
        padding: 12px 14px;
        font-family: inherit;
    }
    .message-card--filled { background: #edfffc; color: #123c3b; }
    .message-card--outlined { border: 1px solid #e9e9e9; background: #fff; color: #414141; }
    .message-card__icon {
        display: block;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        border-radius: 50%;
        background: #fff1e3;
    }
    .message-card--filled .message-card__icon { background: #008f83; }
    .message-card__body { display: flex; min-width: 0; flex-direction: column; gap: 1px; }
    .message-card__value { font-size: 20px; font-weight: 600; line-height: 1.15; }
    .message-card--filled .message-card__value { color: #009688; }
    .message-card__label { font-size: 14px; line-height: 1.3; }
    .message-card__trend { margin-top: 4px; color: #35a65b; font-size: 9px; }
    .message-card__trend span { margin-left: 5px; color: #899295; }
</style>
