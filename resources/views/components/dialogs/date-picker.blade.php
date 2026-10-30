@props([
    'name' => 'date',
    'label' => 'Data',
    'value' => '',
    'min' => null,
    'max' => null,
    'required' => false,
    'disabled' => false,
])

@php($inputId = $attributes->get('id', 'date-' . $name))

<div {{ $attributes->except('id')->class(['jemp-date-field']) }}>
    <label class="jemp-date-field__label" for="{{ $inputId }}">{{ $label }}</label>
    <input
        class="jemp-date-field__input"
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="date"
        value="{{ old($name, $value) }}"
        @if ($min) min="{{ $min }}" @endif
        @if ($max) max="{{ $max }}" @endif
        @required($required)
        @disabled($disabled)
        {{ $attributes->whereStartsWith('aria-') }}>
</div>
