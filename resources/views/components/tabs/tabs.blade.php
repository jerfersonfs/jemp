@props([
    'tabs' => [],
    'active' => null,
    'label' => 'Seções',
    'variant' => 'underline',
])

@php
    $tabsId = $attributes->get('id') ?? 'jemp-tabs-' . \Illuminate\Support\Str::uuid();
    $activeTab = $active ?? ($tabs[0]['id'] ?? null);
    $tabsVariant = in_array($variant, ['underline', 'pills'], true) ? $variant : 'underline';
@endphp

<div {{ $attributes->except('id')->class(['jemp-tabset', "jemp-tabset--{$tabsVariant}"]) }} data-tabs="{{ $tabsId }}">
    <div class="jemp-tabset__list" role="tablist" aria-label="{{ $label }}">
        @foreach ($tabs as $tab)
            <button
                class="jemp-tabset__tab"
                id="{{ $tabsId }}-tab-{{ $tab['id'] }}"
                type="button"
                role="tab"
                aria-controls="{{ $tabsId }}-panel-{{ $tab['id'] }}"
                aria-selected="{{ $tab['id'] === $activeTab ? 'true' : 'false' }}"
                tabindex="{{ $tab['id'] === $activeTab ? '0' : '-1' }}"
                data-tab-target="{{ $tab['id'] }}"
                @disabled($tab['disabled'] ?? false)>{{ $tab['label'] }}</button>
        @endforeach
    </div>

    <div class="jemp-tabset__panels" data-tabs-panels>
        {{ $slot }}
    </div>
</div>
