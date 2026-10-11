@props([
    'name',
    'class' => '',
])

@php
    $paths = [
        'house' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-6h6v6"/>',
        'cube' => '<path d="m12 3 9 5v8l-9 5-9-5V8l9-5Z"/><path d="m3.5 8.5 8.5 5 8.5-5M12 13.5V21"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
        'question' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.5 2.5 0 1 1 4.2 1.8c-1.2 1.1-1.8 1.4-1.8 3M12 17.5h.01"/>',
        'caret-down' => '<path d="m7 10 5 5 5-5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
    ];
@endphp

<svg {{ $attributes->class(['jemp-icon', $class])->merge(['viewBox' => '0 0 24 24', 'width' => '20', 'height' => '20', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true', 'focusable' => 'false']) }}>
    {!! $paths[$name] ?? '' !!}
</svg>
