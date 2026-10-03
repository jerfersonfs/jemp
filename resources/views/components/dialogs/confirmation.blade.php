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

<style>
    .jemp-confirmation {
        --confirm-color: var(--color-danger, #cc2929);
        width: min(100%, 440px);
        box-sizing: border-box;
        border: 1px solid color-mix(in srgb, var(--color-border, #000) 16%, white);
        border-radius: 8px;
        background: white;
        padding: var(--spacing-lg, 24px);
        color: var(--color-caption, #365352);
        font-family: var(--font-family, sans-serif);
        box-shadow: 0 18px 48px rgb(27 64 65 / 16%);
    }

    .jemp-confirmation--primary {
        --confirm-color: var(--color-primary, #0a9680);
    }

    .jemp-confirmation__title {
        margin: 0;
        color: var(--color-heading, #294e4e);
        font-size: var(--font-size-h2, 24px);
    }

    .jemp-confirmation__message {
        margin: var(--spacing-sm, 8px) 0 0;
        font-size: var(--font-size-body, 16px);
        line-height: 1.5;
    }

    .jemp-confirmation__actions {
        display: flex;
        justify-content: flex-end;
        gap: var(--spacing-sm, 8px);
        margin-top: var(--spacing-lg, 24px);
    }

    .jemp-confirmation__button {
        min-height: 38px;
        border: 1px solid var(--color-subheading, #8cb7b8);
        border-radius: 4px;
        background: white;
        padding: 7px 14px;
        color: var(--color-secondary, #1b4041);
        font: inherit;
        cursor: pointer;
    }

    .jemp-confirmation__button--confirm {
        border-color: var(--confirm-color);
        background: var(--confirm-color);
        color: white;
    }
</style>