@props([
'trigger' => 'Abrir opções',
'title' => null,
'placement' => 'bottom',
])

<details {{ $attributes->class(['jemp-popover', "jemp-popover--{$placement}"]) }}>
    <summary class="jemp-popover__trigger">{{ $trigger }}</summary>
    <div class="jemp-popover__panel">
        @if ($title)<h3 class="jemp-popover__title">{{ $title }}</h3>@endif
        {{ $slot }}
    </div>
</details>

<style>
    .jemp-popover {
        position: relative;
        width: fit-content;
        color: var(--color-caption, #365352);
        font-family: var(--font-family, sans-serif);
    }

    .jemp-popover__trigger {
        list-style: none;
        cursor: pointer;
    }

    .jemp-popover__trigger::-webkit-details-marker {
        display: none;
    }

    .jemp-popover__panel {
        position: absolute;
        z-index: 20;
        top: calc(100% + var(--spacing-sm, 8px));
        left: 0;
        width: min(320px, calc(100vw - 32px));
        box-sizing: border-box;
        border: 1px solid var(--color-subheading, #8cb7b8);
        border-radius: 6px;
        background: var(--color-surface, #eefffd);
        padding: var(--spacing-md, 16px);
        box-shadow: 0 12px 30px rgb(27 64 65 / 16%);
    }

    .jemp-popover--top .jemp-popover__panel {
        top: auto;
        bottom: calc(100% + var(--spacing-sm, 8px));
    }

    .jemp-popover__title {
        margin: 0 0 var(--spacing-sm, 8px);
        color: var(--color-heading, #294e4e);
        font-size: var(--font-size-label, 14px);
    }
</style>