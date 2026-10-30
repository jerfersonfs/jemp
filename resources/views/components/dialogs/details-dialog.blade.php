@props([
    'title' => 'Detalhes',
    'description' => null,
    'variant' => 'record',
    'sections' => [],
    'details' => [],
    'columns' => [],
    'rows' => [],
    'statusVariants' => [],
    'closeLabel' => 'Fechar',
])

@php
    $detailsVariant = in_array($variant, ['record', 'indicator'], true) ? $variant : 'record';
    $tabItems = collect($sections)->map(fn ($section) => ['id' => $section['id'], 'label' => $section['label']])->all();
    $tabSetId = $attributes->get('id') ?? 'jemp-details-' . \Illuminate\Support\Str::uuid();
@endphp

<section {{ $attributes->except('id')->class(['jemp-dialog', 'jemp-dialog--large', 'jemp-details-dialog', "jemp-details-dialog--{$detailsVariant}"]) }} role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <header class="jemp-dialog__header">
        <div>
            <h2 class="jemp-dialog__title">{{ $title }}</h2>
            @if ($description)<p class="jemp-dialog__description">{{ $description }}</p>@endif
        </div>
        <button class="jemp-dialog__close" type="button" data-dialog-dismiss aria-label="{{ $closeLabel }}">×</button>
    </header>

    <div class="jemp-details-dialog__body">
        @if ($detailsVariant === 'indicator')
            <x-tables.table
                :columns="$columns"
                :rows="$rows"
                :status-variants="$statusVariants"
                caption="{{ $title }}: registros relacionados"
                variant="rounded" />
        @elseif (count($tabItems))
            <x-tabs.tabs :id="$tabSetId" :tabs="$tabItems" :label="'Seções de ' . $title" variant="underline">
                @foreach ($sections as $section)
                    <section class="jemp-details-dialog__section" data-tab-panel="{{ $section['id'] }}">
                        <dl class="jemp-details-dialog__grid">
                            @foreach ($section['details'] ?? [] as $detail)
                                <div class="jemp-details-dialog__item">
                                    <dt>{{ $detail['label'] }}</dt>
                                    <dd>{{ $detail['value'] ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endforeach
            </x-tabs.tabs>
        @else
            <dl class="jemp-details-dialog__grid">
                @foreach ($details as $detail)
                    <div class="jemp-details-dialog__item">
                        <dt>{{ $detail['label'] }}</dt>
                        <dd>{{ $detail['value'] ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>

    <footer class="jemp-dialog__footer">
        <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-dialog-dismiss>{{ $closeLabel }}</button>
        @isset($footer){{ $footer }}@endisset
    </footer>
</section>
