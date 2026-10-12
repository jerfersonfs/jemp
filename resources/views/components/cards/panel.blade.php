@props([
    'title',
    'description' => null,
    'variant' => 'default',
])

@php($panelVariant = in_array($variant, ['default', 'mint', 'subtle'], true) ? $variant : 'default')

<section {{ $attributes->class(['content-panel', "content-panel--{$panelVariant}"]) }}>
    @if ($title || isset($header))
        <header class="content-panel__header">
            @isset($header)<div class="content-panel__icon">{{ $header }}</div>@endisset
            <div class="content-panel__heading">
                @if ($title)<h2 class="content-panel__title">{{ $title }}</h2>@endif
                @if ($description)<p class="content-panel__description">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="content-panel__actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    @isset($filters)<div class="content-panel__filters">{{ $filters }}</div>@endisset
    <div class="content-panel__body">{{ $slot }}</div>
    @isset($footer)<footer class="content-panel__footer">{{ $footer }}</footer>@endisset
</section>
