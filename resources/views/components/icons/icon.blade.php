@props([
    'name',
    'color' => 'black',
    'size' => 'md',
    'label' => null,
])

@php
    $iconName = preg_replace('/[^a-z0-9-]/', '', strtolower((string) $name));
    $iconColor = in_array($color, ['black', 'white'], true) ? $color : 'black';
    $iconSize = in_array($size, ['sm', 'md', 'lg', 'xl'], true) ? $size : 'md';
@endphp

<span
    {{ $attributes->class(['jemp-icon', "jemp-icon--{$iconSize}", "jemp-icon--{$iconColor}"]) }}
    @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    <i class="ph ph-{{ $iconName }}" aria-hidden="true"></i>
</span>
