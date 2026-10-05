@props([
    'filters' => [],
    'name' => 'filter',
    'selected' => null,
    'action' => '',
    'method' => 'GET',
    'searchName' => 'search',
    'searchLabel' => 'Buscar',
    'searchPlaceholder' => 'Buscar',
    'label' => 'Filtros rápidos',
])

@php($formMethod = strtoupper($method))

<form {{ $attributes->class(['jemp-vertical-filter']) }} action="{{ $action }}" method="{{ in_array($formMethod, ['GET', 'POST'], true) ? strtolower($formMethod) : 'GET' }}" aria-label="{{ $label }}">
    @if ($formMethod === 'POST') @csrf @endif
    <x-inputs.input :name="$searchName" :label="$searchLabel" type="search" :placeholder="$searchPlaceholder" />

    @if (count($filters))
        <fieldset class="jemp-vertical-filter__options">
            <legend>{{ $label }}</legend>
            @foreach ($filters as $filter)
                <label class="jemp-vertical-filter__option">
                    <input type="radio" name="{{ $name }}" value="{{ $filter['value'] }}" @checked((string) $filter['value'] === (string) $selected)>
                    <span>{{ $filter['label'] }}</span>
                </label>
            @endforeach
        </fieldset>
    @endif

    @if ($slot->isNotEmpty())
        <div class="jemp-vertical-filter__actions">{{ $slot }}</div>
    @endif
</form>
