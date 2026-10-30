@props([
'title' => 'Título do dialog',
'description' => null,
'size' => 'medium',
'submitLabel' => 'Salvar',
'cancelLabel' => 'Cancelar',
'showActions' => true,
])

<section {{ $attributes->class(['jemp-dialog', "jemp-dialog--{$size}"]) }} role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <header class="jemp-dialog__header">
        <div>
            <h2 class="jemp-dialog__title">{{ $title }}</h2>
            @if ($description)<p class="jemp-dialog__description">{{ $description }}</p>@endif
        </div>
        <button class="jemp-dialog__close" type="button" aria-label="Fechar">×</button>
    </header>
    <div class="jemp-dialog__body">{{ $slot }}</div>
    @if ($showActions)
    <footer class="jemp-dialog__footer">
        <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button">{{ $cancelLabel }}</button>
        <button class="jemp-dialog__button jemp-dialog__button--primary" type="button">{{ $submitLabel }}</button>
    </footer>
    @endif
</section>