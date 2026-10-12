@props([
    'columns' => [],
    'rows' => [],
    'caption' => null,
    'emptyMessage' => 'Nenhum registro encontrado.',
    'statusVariants' => [],
    'variant' => 'rounded',
])

@php
    $tableVariant = in_array($variant, ['rounded', 'filtered', 'spreadsheet'], true) ? $variant : 'rounded';
@endphp

<div {{ $attributes->class(['jemp-table-region', "jemp-table-region--{$tableVariant}"]) }}>
    @isset($filters)
        <div class="jemp-table-region__filters">{{ $filters }}</div>
    @endisset
    <div class="jemp-table-scroll" role="region" tabindex="0" @if ($caption) aria-label="{{ $caption }}" @else aria-label="Tabela de dados" @endif>
        <table class="jemp-table">
            @if ($caption)<caption class="jemp-visually-hidden">{{ $caption }}</caption>@endif
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col" class="{{ $column['class'] ?? '' }}">{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $record = \Illuminate\Support\Arr::except($row, ['_data']);
                        $rowDataAttributes = collect($row['_data'] ?? [])
                            ->map(fn ($value, $key) => 'data-'.preg_replace('/[^a-z0-9-]/', '', strtolower((string) $key)).'="'.e($value).'"')
                            ->implode(' ');
                    @endphp
                    <tr data-table-row data-record="{{ json_encode($record, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}" {!! $rowDataAttributes !!}>
                        @foreach ($columns as $column)
                            @php
                                $cellValue = data_get($row, $column['key']);
                                $statusVariant = $statusVariants[(string) $cellValue] ?? 'neutral';
                                if (!in_array($statusVariant, ['success', 'warning', 'danger', 'info', 'neutral'], true)) {
                                    $statusVariant = 'neutral';
                                }
                            @endphp
                            <td class="{{ $column['class'] ?? '' }}" data-column-key="{{ $column['key'] }}" data-column-type="{{ $column['type'] ?? 'text' }}">
                                @if (($column['type'] ?? null) === 'status')
                                    <x-status.badge :label="$cellValue" :variant="$statusVariant" />
                                @elseif (($column['type'] ?? null) === 'detail')
                                    @php
                                        $dialogTarget = data_get($row, $column['targetKey'] ?? '') ?? ($column['target'] ?? null);
                                    @endphp
                                    @if ($dialogTarget)
                                        <button class="jemp-table__detail" type="button" data-dialog-open="{{ $dialogTarget }}">{{ $column['detailLabel'] ?? 'Ver detalhes' }}</button>
                                    @endif
                                @elseif (($column['type'] ?? null) === 'actions')
                                    <div class="jemp-table__actions">
                                        @foreach (($column['actions'] ?? ['edit', 'delete']) as $action)
                                            @php
                                                $actionConfig = is_array($action) ? $action : ['name' => $action];
                                                $actionName = $actionConfig['name'] ?? 'action';
                                                $actionIcon = match ($actionName) {
                                                    'edit' => 'pencil-simple',
                                                    'delete' => 'trash',
                                                    'view' => 'eye',
                                                    default => 'dots-three',
                                                };
                                                $actionLabel = $actionConfig['label'] ?? match ($actionName) {
                                                    'edit' => 'Editar',
                                                    'delete' => 'Excluir',
                                                    'view' => 'Visualizar',
                                                    default => 'Ação',
                                                };
                                            @endphp
                                            <button class="jemp-table__icon-action jemp-table__icon-action--{{ $actionName }}" type="button" data-movement-action="{{ $actionName }}" aria-label="{{ $actionLabel }}: {{ data_get($row, $column['labelKey'] ?? 'code') }}" title="{{ $actionLabel }}">
                                                <i class="ph ph-{{ $actionIcon }}" aria-hidden="true"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif (($column['type'] ?? null) === 'input')
                                    <input
                                        class="jemp-table__inline-input"
                                        type="{{ $column['inputType'] ?? 'text' }}"
                                        @if (isset($column['name'])) name="{{ $column['name'] }}" @endif
                                        value="{{ $cellValue }}"
                                        aria-label="{{ $column['label'] }}"
                                        @if (isset($column['min'])) min="{{ $column['min'] }}" @endif
                                        @if (isset($column['step'])) step="{{ $column['step'] }}" @endif
                                        data-line-input>
                                @elseif (($column['type'] ?? null) === 'computed-stock')
                                    <span data-stock-output>{{ $cellValue }}</span>
                                @else
                                    {{ $cellValue }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="jemp-table__empty" colspan="{{ max(count($columns), 1) }}">{{ $emptyMessage }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @isset($footer)
        <div class="jemp-table-region__footer">{{ $footer }}</div>
    @endisset
</div>
