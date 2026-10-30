@props([
    'text' => '',
    'placement' => 'top',
])

@php
    $tooltipPlacement = in_array($placement, ['top', 'bottom'], true) ? $placement : 'top';
    $tooltipId = $attributes->get('id') ?? 'jemp-tooltip-' . \Illuminate\Support\Str::uuid();
@endphp

<span {{ $attributes->except('id')->class(['jemp-tooltip', "jemp-tooltip--{$tooltipPlacement}"]) }} tabindex="0" aria-describedby="{{ $tooltipId }}">
    {{ $slot }}
    <span class="jemp-tooltip__content" id="{{ $tooltipId }}" role="tooltip">{{ $text }}</span>
</span>
