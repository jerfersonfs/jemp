@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'options' => [],
    'size' => 'md',
    'rows' => 4,
])

@php
    $fieldId = $attributes->get('id') ?? ($name ?: 'input-' . \Illuminate\Support\Str::uuid());
    $hintText = $hint;
    $errorText = $error ?? ($name ? $errors->first($name) : null);
    $fieldValue = $name ? old($name, $value) : $value;
    $descriptionIds = collect([
        $hintText ? "{$fieldId}-hint" : null,
        $errorText ? "{$fieldId}-error" : null,
    ])->filter()->implode(' ');
@endphp

<div class="jemp-input jemp-input--{{ $size }} {{ $errorText ? 'jemp-input--error' : '' }}">
    @if ($label)
        <label class="jemp-input__label" for="{{ $fieldId }}">
            {{ $label }}
            @if ($required)<span class="jemp-input__required" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <div class="jemp-input__control-wrap">
        @isset($leading)
            <span class="jemp-input__addon" aria-hidden="true">{{ $leading }}</span>
        @endisset

        @if ($type === 'select')
            <select
                id="{{ $fieldId }}"
                name="{{ $name }}"
                class="jemp-input__control {{ $attributes->get('class') }}"
                @if ($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif
                @if ($errorText) aria-invalid="true" @endif
                @required($required)
                @disabled($disabled)
                data-select-control
                {{ $attributes->except(['id', 'class', 'aria-describedby', 'aria-invalid']) }}>
                @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
                @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
            </select>
            <span class="jemp-input__select-icon" aria-hidden="true"><i class="ph ph-caret-down"></i></span>
        @elseif ($type === 'textarea')
            <textarea
                id="{{ $fieldId }}"
                name="{{ $name }}"
                class="jemp-input__control jemp-input__textarea {{ $attributes->get('class') }}"
                rows="{{ $rows }}"
                placeholder="{{ $placeholder }}"
                @if ($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif
                @if ($errorText) aria-invalid="true" @endif
                @required($required)
                @disabled($disabled)
                @readonly($readonly)
                {{ $attributes->except(['id', 'class', 'aria-describedby', 'aria-invalid']) }}>{{ $fieldValue }}</textarea>
        @else
            <input
                id="{{ $fieldId }}"
                name="{{ $name }}"
                type="{{ $type }}"
                class="jemp-input__control {{ $attributes->get('class') }}"
                value="{{ $fieldValue }}"
                placeholder="{{ $placeholder }}"
                @if ($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif
                @if ($errorText) aria-invalid="true" @endif
                @required($required)
                @disabled($disabled)
                @readonly($readonly)
                {{ $attributes->except(['id', 'class', 'aria-describedby', 'aria-invalid']) }}>
        @endif

        @isset($trailing)
            <span class="jemp-input__addon">{{ $trailing }}</span>
        @endisset
    </div>

    @if ($hintText)<p class="jemp-input__hint" id="{{ $fieldId }}-hint">{{ $hintText }}</p>@endif
    @if ($errorText)<p class="jemp-input__error" id="{{ $fieldId }}-error" role="alert">{{ $errorText }}</p>@endif
</div>
