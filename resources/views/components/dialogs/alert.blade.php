@props([
    'variant' => 'warning',
    'title' => null,
    'message' => 'A operação foi concluída.',
    'dismissible' => true,
    'icon' => null,
])

@php
    $icons = ['warning' => 'warning', 'error' => 'x-circle', 'success' => 'check-circle', 'info' => 'info', 'bell' => 'bell'];
    $iconName = $icon ?? ($icons[$variant] ?? 'info');
@endphp

<div {{ $attributes->class(['jemp-notice', "jemp-notice--{$variant}"]) }} role="{{ $variant === 'error' ? 'alert' : 'status' }}">
    <span class="jemp-notice__icon" aria-hidden="true"><x-icons.icon :name="$iconName" size="sm" /></span>
    <div class="jemp-notice__content">
        @if ($title)<strong class="jemp-notice__title">{{ $title }}</strong>@endif
        <span class="jemp-notice__message">{{ $message }}</span>
    </div>
    @if ($slot->isNotEmpty())<div class="jemp-notice__actions">{{ $slot }}</div>@endif
    @if ($dismissible)<button class="jemp-notice__close" type="button" aria-label="Fechar notificação">×</button>@endif
</div>
