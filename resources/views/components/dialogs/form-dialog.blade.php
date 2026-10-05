@props([
    'title' => null,
    'description' => null,
    'submitLabel' => null,
    'action' => '',
    'method' => 'POST',
    'fields' => [],
    'mode' => 'create',
])

@php
    $editing = $mode === 'edit';
    $formFields = $fields ?? [];
    $formMethod = strtoupper($method);
    $dialogTitle = $title ?? ($editing ? 'Editar registro' : 'Novo registro');
    $submitText = $submitLabel ?? ($editing ? 'Salvar alterações' : 'Cadastrar');
@endphp

<x-dialogs.dialog
    :title="$dialogTitle"
    :description="$description"
    :show-actions="false"
    {{ $attributes->class(['jemp-dialog--editing' => $editing]) }}>
    <form class="jemp-form-dialog {{ $editing ? 'jemp-form-dialog--edit' : 'jemp-form-dialog--create' }}" action="{{ $action }}" method="{{ in_array($formMethod, ['GET', 'POST'], true) ? strtolower($formMethod) : 'POST' }}">
        @if (!in_array($formMethod, ['GET', 'POST'], true))
            @method($formMethod)
        @endif
        @if (!in_array($formMethod, ['GET'], true))
            @csrf
        @endif

        @foreach ($formFields as $field)
            @php
                $fieldName = $field['name'];
                $fieldType = $field['type'] ?? 'text';
                $fieldId = $field['id'] ?? 'field-' . str_replace(['[', ']'], '', $fieldName);
                $fieldValue = old($fieldName, $field['value'] ?? '');
            @endphp
            <div class="jemp-form-dialog__field">
                <label for="{{ $fieldId }}">{{ $field['label'] }}</label>
                @if ($fieldType === 'textarea')
                    <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" placeholder="{{ $field['placeholder'] ?? '' }}" @required($field['required'] ?? false) @disabled($field['disabled'] ?? false) aria-invalid="@error($fieldName)true @else false @enderror">{{ $fieldValue }}</textarea>
                @elseif ($fieldType === 'select')
                    <select id="{{ $fieldId }}" name="{{ $fieldName }}" @required($field['required'] ?? false) @disabled($field['disabled'] ?? false) aria-invalid="@error($fieldName)true @else false @enderror">
                        @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        id="{{ $fieldId }}"
                        name="{{ $fieldName }}"
                        type="{{ $fieldType }}"
                        value="{{ $fieldValue }}"
                        placeholder="{{ $field['placeholder'] ?? '' }}"
                        autocomplete="{{ $field['autocomplete'] ?? 'off' }}"
                        @readonly($field['readonly'] ?? false)
                        @required($field['required'] ?? false)
                        @disabled($field['disabled'] ?? false)
                        aria-invalid="@error($fieldName)true @else false @enderror">
                @endif
                @error($fieldName)
                    <span class="jemp-form-dialog__error" role="alert">{{ $message }}</span>
                @enderror
            </div>
        @endforeach

        <footer class="jemp-form-dialog__footer">
            <button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">{{ $submitText }}</button>
        </footer>
    </form>
</x-dialogs.dialog>
