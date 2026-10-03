@props([
'idPrefix' => 'product',
'mode' => 'create',
'values' => [],
'types' => ['Peça', 'Matéria-prima', 'Componente'],
'locations' => ['Barracão 1', 'Barracão 2'],
])

@php
$editing = $mode === 'edit';
$title = $editing ? 'Editar Produto' : 'Cadastro de Produtos';
$submit = $editing ? 'Salvar' : 'Cadastrar';
$fields = [
['name' => 'code', 'label' => 'Código', 'value' => $values['code'] ?? '001'],
['name' => 'product', 'label' => 'Produto', 'value' => $values['product'] ?? 'Palete'],
['name' => 'type', 'label' => 'Tipo', 'value' => $values['type'] ?? 'Peça'],
['name' => 'width', 'label' => 'Largura', 'value' => $values['width'] ?? '1'],
['name' => 'length', 'label' => 'Comprimento', 'value' => $values['length'] ?? '1,20'],
['name' => 'quantity', 'label' => 'Quantidade', 'value' => $values['quantity'] ?? '10'],
['name' => 'location', 'label' => 'Local', 'value' => $values['location'] ?? 'Barracão 1'],
];
@endphp

<section {{ $attributes->class(['jemp-product-dialog', $editing ? 'jemp-product-dialog--edit' : '']) }} role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <header class="jemp-product-dialog__header">
        <h2>{{ $title }}</h2>
        <button type="button" aria-label="Fechar">×</button>
    </header>
    <form class="jemp-product-dialog__form" method="post">
        @csrf
        @foreach ($fields as $field)
        <label class="jemp-product-dialog__field" for="{{ $idPrefix }}-{{ $field['name'] }}">
            <span>{{ $field['label'] }}</span>
            @if ($field['name'] === 'type' || $field['name'] === 'location')
            @php $options = $field['name'] === 'type' ? $types : $locations; @endphp
            <select id="{{ $idPrefix }}-{{ $field['name'] }}" name="{{ $field['name'] }}">
                @foreach ($options as $option)<option @selected($option===$field['value'])>{{ $option }}</option>@endforeach
            </select>
            @else
            <input id="{{ $idPrefix }}-{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['value'] }}" @if ($field['name']==='code' ) readonly @endif>
            @endif
        </label>
        @endforeach
        <button class="jemp-product-dialog__submit" type="submit">{{ $submit }}</button>
    </form>
</section>

<style>
    .jemp-product-dialog {
        width: min(100%, 360px);
        box-sizing: border-box;
        border: 1px solid color-mix(in srgb, var(--color-border, #000) 16%, white);
        border-radius: 6px;
        background: var(--color-surface, #eefffd);
        padding: var(--spacing-md, 16px);
        color: var(--color-caption, #365352);
        font-family: var(--font-family, sans-serif);
        box-shadow: 0 16px 40px rgb(27 64 65 / 14%);
    }

    .jemp-product-dialog__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--spacing-sm, 8px);
        margin-bottom: var(--spacing-md, 16px);
    }

    .jemp-product-dialog__header h2 {
        margin: 0;
        color: var(--color-primary, #0a9680);
        font-size: 20px;
    }

    .jemp-product-dialog--edit .jemp-product-dialog__header h2 {
        color: var(--color-info, #2563eb);
    }

    .jemp-product-dialog__header button {
        border: 0;
        background: transparent;
        color: var(--color-caption, #365352);
        font-size: 22px;
        cursor: pointer;
    }

    .jemp-product-dialog__form {
        display: grid;
        gap: 10px;
    }

    .jemp-product-dialog__field {
        display: grid;
        grid-template-columns: minmax(84px, 1fr) minmax(0, 1.3fr);
        align-items: center;
        gap: 10px;
        font-size: var(--font-size-caption, 12px);
    }

    .jemp-product-dialog__field input,
    .jemp-product-dialog__field select {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        border: 1px solid var(--color-subheading, #8cb7b8);
        border-radius: 3px;
        background: white;
        padding: 7px 8px;
        color: var(--color-secondary, #1b4041);
        font: inherit;
    }

    .jemp-product-dialog__field input[readonly] {
        background: var(--color-background, #f3f7f8);
    }

    .jemp-product-dialog__submit {
        justify-self: center;
        min-width: 112px;
        margin-top: 4px;
        border: 0;
        border-radius: 4px;
        background: var(--color-primary, #0a9680);
        padding: 9px 16px;
        color: white;
        font: inherit;
        font-size: var(--font-size-button, 14px);
        cursor: pointer;
    }

    .jemp-product-dialog--edit .jemp-product-dialog__submit {
        background: var(--color-info, #2563eb);
    }

    @media (max-width: 420px) {
        .jemp-product-dialog__field {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>
