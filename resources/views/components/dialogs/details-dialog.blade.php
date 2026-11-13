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
                        @if (isset($section['items']))
                            @if ($section['items_locked'] ?? false)
                                <p class="invoice-import-help">Itens bloqueados: a saída de estoque desta nota já foi registrada.</p>
                            @endif
                            <div class="invoice-items-table-wrap" role="region" aria-label="Itens da nota fiscal" tabindex="0">
                                <table class="invoice-items-table">
                                    <thead><tr><th>Produto</th><th>SKU</th><th>Quantidade</th><th>Preço unitário</th><th>Subtotal</th><th>Ações</th></tr></thead>
                                    <tbody>
                                        @forelse ($section['items'] as $item)
                                            <tr>
                                                <td>{{ $item['product'] }}@if (!empty($item['external_code']))<small class="invoice-item-source">XML: {{ $item['external_code'] }} · {{ $item['external_description'] }}</small>@endif</td><td>{{ $item['sku'] }}</td><td>{{ $item['quantity'] }}</td><td>{{ $item['unit_price_display'] }}</td><td>{{ $item['subtotal'] }}</td>
                                                <td><div class="invoice-item-actions">
                                                    @if ($section['items_locked'] ?? false)
                                                        <span title="Saída já registrada">Bloqueado</span>
                                                    @else
                                                    <button type="button" data-invoice-item-action="edit" data-item-id="{{ $item['id'] }}" data-product-id="{{ $item['product_id'] }}" data-quantity="{{ $item['quantity'] }}" data-unit-price="{{ $item['unit_price'] }}" aria-label="Editar item {{ $item['product'] }}" title="Editar item"><i class="ph ph-pencil-simple" aria-hidden="true"></i></button>
                                                    <button type="button" data-invoice-item-action="delete" data-item-id="{{ $item['id'] }}" data-item-label="{{ $item['product'] }}" aria-label="Excluir item {{ $item['product'] }}" title="Excluir item"><i class="ph ph-trash" aria-hidden="true"></i></button>
                                                    @endif
                                                </div></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="invoice-items-table__empty">Nenhum item cadastrado nesta nota.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <dl class="jemp-details-dialog__grid">
                                @foreach ($section['details'] ?? [] as $detail)
                                    <div class="jemp-details-dialog__item"><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] ?? '—' }}</dd></div>
                                @endforeach
                            </dl>
                        @endif
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

    {{ $slot }}

    <footer class="jemp-dialog__footer">
        <button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-dialog-dismiss>{{ $closeLabel }}</button>
        @isset($footer){{ $footer }}@endisset
    </footer>
</section>
