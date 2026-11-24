@props([
    'action' => '',
    'method' => 'GET',
    'label' => 'Filtros',
    'wideSearch' => false,
])

@php($formMethod = strtoupper($method))

<form {{ $attributes->class(['jemp-filter-bar', 'jemp-filter-bar--search' => $wideSearch]) }} action="{{ $action }}" method="{{ in_array($formMethod, ['GET', 'POST'], true) ? strtolower($formMethod) : 'GET' }}" role="search" aria-label="{{ $label }}">
    @if ($formMethod === 'POST') @csrf @endif
    <div class="jemp-filter-bar__fields">{{ $slot }}</div>
    @isset($actions)
    <div class="jemp-filter-bar__actions">{{ $actions }}</div>
    @endisset
</form>
