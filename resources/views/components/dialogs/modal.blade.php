@props(['label' => 'Diálogo'])

<dialog {{ $attributes->class(['jemp-modal']) }} aria-label="{{ $label }}">
    {{ $slot }}
</dialog>
