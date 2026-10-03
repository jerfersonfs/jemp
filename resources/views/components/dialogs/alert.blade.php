@props([
    'variant' => 'warning',
    'title' => null,
    'message' => 'A operação foi concluída.',
    'dismissible' => true,
    'icon' => null,
])

@php
    $icons = ['warning' => '!', 'error' => '⊘', 'success' => '✓', 'info' => 'i', 'bell' => '♧'];
@endphp

<div {{ $attributes->class(['jemp-notice', "jemp-notice--{$variant}"]) }} role="{{ $variant === 'error' ? 'alert' : 'status' }}">
    <span class="jemp-notice__icon" aria-hidden="true">{{ $icon ?? ($icons[$variant] ?? 'i') }}</span>
    <div class="jemp-notice__content">
        @if ($title)<strong class="jemp-notice__title">{{ $title }}</strong>@endif
        <span class="jemp-notice__message">{{ $message }}</span>
    </div>
    @if ($slot->isNotEmpty())<div class="jemp-notice__actions">{{ $slot }}</div>@endif
    @if ($dismissible)<button class="jemp-notice__close" type="button" aria-label="Fechar notificação">×</button>@endif
</div>

<style>
    .jemp-notice {
        --notice-color: var(--color-warning, #e17c00);
        --notice-bg: #fff5ec;
        display: flex; min-height: 56px; align-items: center; gap: var(--spacing-sm, 8px);
        border: 1px solid var(--notice-color); border-radius: 2px; background: var(--notice-bg);
        padding: 10px 14px; color: var(--notice-color); font-family: var(--font-family, sans-serif);
    }
    .jemp-notice--error { --notice-color: var(--color-danger, #cc2929); --notice-bg: #ffe4e4; }
    .jemp-notice--success { --notice-color: var(--color-success, #2e7d32); --notice-bg: #effcf3; }
    .jemp-notice--info { --notice-color: var(--color-info, #2563eb); --notice-bg: #eef3ff; }
    .jemp-notice--bell { --notice-color: #1597c4; --notice-bg: #f1fbff; }
    .jemp-notice__icon { display: grid; width: 28px; height: 28px; flex: 0 0 28px; place-items: center; font-size: 23px; font-weight: 700; }
    .jemp-notice__content { display: flex; min-width: 0; flex: 1; flex-direction: column; gap: 2px; }
    .jemp-notice__title { font-size: var(--font-size-label, 14px); }
    .jemp-notice__message { font-size: var(--font-size-body, 16px); line-height: 1.4; }
    .jemp-notice__close { border: 0; background: transparent; color: #263635; cursor: pointer; font: inherit; font-size: 26px; line-height: 1; }
    .jemp-notice__actions { display: flex; align-items: center; gap: var(--spacing-sm, 8px); }
    @media (max-width: 520px) { .jemp-notice { align-items: flex-start; } .jemp-notice__message { font-size: 14px; } }
</style>
