@props([
'title' => 'Confirmar ação',
'message' => 'Deseja continuar? Esta ação não poderá ser desfeita.',
'confirmLabel' => 'Confirmar',
'cancelLabel' => 'Cancelar',
'variant' => 'danger',
])

<section {{ $attributes->class(['jemp-confirmation', "jemp-confirmation--{$variant}"]) }} role="alertdialog" aria-modal="true" aria-label="{{ $title }}">
    <h2 class="jemp-confirmation__title">{{ $title }}</h2>
    <p class="jemp-confirmation__message">{{ $message }}</p>
    <div class="jemp-confirmation__actions">
        <button class="jemp-confirmation__button jemp-confirmation__button--cancel" type="button">{{ $cancelLabel }}</button>
        <button class="jemp-confirmation__button jemp-confirmation__button--confirm" type="button">{{ $confirmLabel }}</button>
    </div>
</section>