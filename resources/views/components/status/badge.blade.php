@props([
    'label' => '',
    'variant' => 'neutral',
])

@php($statusVariant = in_array($variant, ['success', 'warning', 'danger', 'info', 'neutral'], true) ? $variant : 'neutral')

<span {{ $attributes->class(['jemp-status-badge', "jemp-status-badge--{$statusVariant}"]) }}>
    <span class="jemp-status-badge__dot" aria-hidden="true"></span>
    <span>{{ $label }}</span>
</span>
