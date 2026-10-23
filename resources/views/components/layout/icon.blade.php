@props([
    'name',
])

@php
    $icons = [
        'house' => 'ph-house',
        'cube' => 'ph-cube',
        'users-three' => 'ph-users-three',
        'note-pencil' => 'ph-note-pencil',
        'handshake' => 'ph-handshake',
        'tray-arrow-down' => 'ph-tray-arrow-down',
        'tray-arrow-up' => 'ph-tray-arrow-up',
        'arrows-left-right' => 'ph-arrows-left-right',
        'clipboard-text' => 'ph-clipboard-text',
        'question' => 'ph-question',
        'caret-down' => 'ph-caret-down',
        'menu' => 'ph-list',
        'sidebar-simple' => 'ph-sidebar-simple',
        'user' => 'ph-user',
        'lock' => 'ph-lock-key',
        'eye' => 'ph-eye',
    ];
@endphp

<i {{ $attributes->class(['jemp-icon', 'ph', $icons[$name] ?? 'ph-question'])->merge(['aria-hidden' => 'true']) }}></i>
