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
                    <tr>
                        @foreach ($columns as $column)
                            @php
                                $cellValue = data_get($row, $column['key']);
                                $statusVariant = $statusVariants[(string) $cellValue] ?? 'neutral';
                                if (!in_array($statusVariant, ['success', 'warning', 'danger', 'info', 'neutral'], true)) {
                                    $statusVariant = 'neutral';
                                }
                            @endphp
                            <td class="{{ $column['class'] ?? '' }}">
                                @if (($column['type'] ?? null) === 'status')
                                    <span class="jemp-table__status jemp-table__status--{{ $statusVariant }}">{{ $cellValue }}</span>
                                @elseif (($column['type'] ?? null) === 'detail')
                                    @php($dialogTarget = data_get($row, $column['targetKey'] ?? '') ?? ($column['target'] ?? null))
                                    @if ($dialogTarget)
                                        <button class="jemp-table__detail" type="button" data-dialog-open="{{ $dialogTarget }}">{{ $column['detailLabel'] ?? 'Ver detalhes' }}</button>
                                    @endif
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
