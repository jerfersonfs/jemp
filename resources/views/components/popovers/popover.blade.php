@props([
    'title' => 'Detalhes',
    'description' => null,
    'triggerLabel' => 'Abrir opções',
    'size' => 'regular',
])

@php
    $popoverSize = in_array($size, ['regular', 'wide'], true) ? $size : 'regular';
    $popoverId = $attributes->get('id') ?? 'jemp-popover-' . \Illuminate\Support\Str::uuid();
@endphp

<div {{ $attributes->except('id')->class(['jemp-popover', "jemp-popover--{$popoverSize}"]) }} data-popover>
    <button class="jemp-popover__trigger" type="button" data-popover-trigger aria-controls="{{ $popoverId }}-panel" aria-expanded="false">
        @isset($trigger){{ $trigger }}@else{{ $triggerLabel }}@endisset
    </button>
    <section class="jemp-popover__panel" id="{{ $popoverId }}-panel" role="dialog" aria-labelledby="{{ $popoverId }}-title" @if ($description) aria-describedby="{{ $popoverId }}-description" @endif hidden data-popover-panel>
        <header class="jemp-popover__header">
            <div>
                <h2 id="{{ $popoverId }}-title">{{ $title }}</h2>
                @if ($description)<p id="{{ $popoverId }}-description">{{ $description }}</p>@endif
            </div>
            <button class="jemp-popover__close" type="button" data-popover-close aria-label="Fechar">×</button>
        </header>
        <div class="jemp-popover__body">{{ $slot }}</div>
        @isset($actions)<footer class="jemp-popover__actions">{{ $actions }}</footer>@endisset
    </section>
</div>
