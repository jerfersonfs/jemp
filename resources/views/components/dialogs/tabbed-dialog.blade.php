@props(['firstLabel' => 'Acesso', 'secondLabel' => 'Senha'])

@php $secondContent = isset($second) ? $second : ''; @endphp

<section {{ $attributes->class(['jemp-tabbed-dialog']) }}>
    <div class="jemp-tabs" role="tablist" aria-label="Seções do dialog">
        <input type="radio" name="jemp-dialog-tab-{{ $attributes->get('id', 'default') }}" id="jemp-tab-a-{{ $attributes->get('id', 'default') }}" checked>
        <label role="tab" for="jemp-tab-a-{{ $attributes->get('id', 'default') }}">{{ $firstLabel }}</label>
        <input type="radio" name="jemp-dialog-tab-{{ $attributes->get('id', 'default') }}" id="jemp-tab-b-{{ $attributes->get('id', 'default') }}">
        <label role="tab" for="jemp-tab-b-{{ $attributes->get('id', 'default') }}">{{ $secondLabel }}</label>
        <div class="jemp-tabs__panels">
            <div class="jemp-tabs__panel">{{ $slot }}</div>
            <div class="jemp-tabs__panel">{{ $secondContent }}</div>
        </div>
    </div>
</section>

<style>
    .jemp-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        font-family: var(--font-family, sans-serif);
    }

    .jemp-tabs>input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .jemp-tabs>label {
        border: 1px solid var(--color-subheading, #8cb7b8);
        border-radius: 4px 4px 0 0;
        background: white;
        padding: 7px 12px;
        color: var(--color-secondary, #1b4041);
        font-size: var(--font-size-button, 14px);
        cursor: pointer;
    }

    .jemp-tabs>input:checked+label {
        border-color: var(--color-primary, #0a9680);
        background: var(--color-primary, #0a9680);
        color: white;
    }

    .jemp-tabs__panels {
        flex-basis: 100%;
        border: 1px solid var(--color-subheading, #8cb7b8);
        border-radius: 0 4px 4px 4px;
        background: var(--color-surface, #eefffd);
        padding: var(--spacing-md, 16px);
    }

    .jemp-tabs__panel:nth-child(2) {
        display: none;
    }

    .jemp-tabs>input:nth-of-type(2):checked~.jemp-tabs__panels .jemp-tabs__panel:first-child {
        display: none;
    }

    .jemp-tabs>input:nth-of-type(2):checked~.jemp-tabs__panels .jemp-tabs__panel:nth-child(2) {
        display: block;
    }
</style>