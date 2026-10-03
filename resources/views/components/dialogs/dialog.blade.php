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

<style>
    .jemp-dialog {
        --dialog-accent: var(--color-primary, #0a9680);
        width: min(100%, 560px);
        box-sizing: border-box;
        border: 1px solid color-mix(in srgb, var(--color-border, #000) 16%, white);
        border-radius: 8px;
        background: var(--color-surface, #eefffd);
        color: var(--color-caption, #365352);
        padding: var(--spacing-lg, 24px);
        font-family: var(--font-family, sans-serif);
        box-shadow: 0 18px 48px rgb(27 64 65 / 16%);
    }

    .jemp-dialog--small {
        width: min(100%, 420px);
    }

    .jemp-dialog--large {
        width: min(100%, 680px);
    }

    .jemp-dialog__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: var(--spacing-md, 16px);
    }

    .jemp-dialog__title {
        margin: 0;
        color: var(--dialog-accent);
        font-size: var(--font-size-h2, 24px);
        font-weight: 700;
        line-height: 1.25;
    }

    .jemp-dialog__description {
        margin: var(--spacing-xs, 4px) 0 0;
        color: var(--color-caption, #365352);
        font-size: var(--font-size-body, 16px);
        line-height: 1.5;
    }

    .jemp-dialog__close {
        flex: 0 0 auto;
        border: 0;
        background: transparent;
        color: var(--color-caption, #365352);
        cursor: pointer;
        font: inherit;
        font-size: 24px;
        line-height: 1;
    }

    .jemp-dialog__body {
        margin-top: var(--spacing-lg, 24px);
    }

    .jemp-dialog__footer {
        display: flex;
        justify-content: flex-end;
        gap: var(--spacing-sm, 8px);
        margin-top: var(--spacing-lg, 24px);
    }

    .jemp-dialog__button {
        min-height: 40px;
        border: 1px solid transparent;
        border-radius: 4px;
        padding: 8px 16px;
        font: inherit;
        font-size: var(--font-size-button, 14px);
        cursor: pointer;
    }

    .jemp-dialog__button--primary {
        background: var(--dialog-accent);
        color: white;
    }

    .jemp-dialog__button--secondary {
        border-color: var(--color-subheading, #8cb7b8);
        background: white;
        color: var(--color-secondary, #1b4041);
    }

    @media (max-width: 520px) {
        .jemp-dialog {
            padding: var(--spacing-md, 16px);
        }

        .jemp-dialog__footer {
            flex-wrap: wrap;
        }
    }
</style>