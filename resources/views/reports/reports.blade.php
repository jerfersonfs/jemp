@php
foreach ($reportSections as $sectionKey => $section) {
    foreach ($section['rows'] as $rowIndex => $row) {
        $reportSections[$sectionKey]['rows'][$rowIndex]['detailTarget'] = 'report-record-details';
    }
}
@endphp

<x-layouts.app title="Relatórios · JEMP" page="reports">
    <section class="reports-page" data-reports-page data-report-config="{{ Illuminate\Support\Js::encode($reportSections) }}">
        <header class="reports-page__heading">
            <h1>Relatórios</h1>
        </header>

        <div class="reports-workspace">
            <aside class="reports-workspace__filters" aria-label="Filtros dos relatórios">
                <h2 class="reports-workspace__filter-title">Filtre as informações</h2>
                <x-filters.vertical-filter
                    name="preset"
                    label="Filtros pré-gerados"
                    search-label="Buscar"
                    search-placeholder="Buscar"
                    :filters="$reportSections['overview']['presets']"
                    selected="all"
                    data-report-filter-form>
                </x-filters.vertical-filter>
            </aside>

            <div class="reports-workspace__content">
                <x-tabs.tabs id="reports-tabs" label="Seções dos relatórios" variant="pills" :tabs="collect($reportSections)->map(fn ($section, $key) => ['id' => $key, 'label' => $section['label']])->values()->all()">
                    @foreach ($reportSections as $sectionKey => $section)
                        <section class="reports-panel" data-tab-panel="{{ $sectionKey }}" hidden>
                            <div class="reports-panel__metrics" data-report-metrics>
                                @foreach ($section['metrics'] as $metricIndex => $metric)
                                    @if ($metricIndex === 0)
                                        <x-cards.stat-card
                                            :label="$metric['label']"
                                            :value="$metric['value']"
                                            size="large"
                                            variant="default"
                                            details-target="report-indicator-details"
                                            data-report-metric="{{ $metricIndex }}"
                                            data-metric-source="{{ $metric['source'] }}" />
                                    @endif
                                @endforeach
                                <div class="reports-panel__supporting-metrics">
                                    <p class="reports-panel__description" data-report-description>{{ $section['description'] }}</p>
                                    <div class="reports-panel__metric-grid">
                                        @foreach ($section['metrics'] as $metricIndex => $metric)
                                            @if ($metricIndex > 0)
                                                <x-cards.stat-card
                                                    :label="$metric['label']"
                                                    :value="$metric['value']"
                                                    size="regular"
                                                    variant="mint"
                                                    details-target="report-indicator-details"
                                                    data-report-metric="{{ $metricIndex }}"
                                                    data-metric-source="{{ $metric['source'] }}" />
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="reports-panel__table">
                                <x-tables.table
                                    variant="rounded"
                                    :columns="$section['columns']"
                                    :rows="$section['rows']"
                                    :status-variants="$section['statusVariants']"
                                    :caption="'Relatório: '.$section['label']"
                                    data-report-table />
                                <footer class="reports-pagination" data-report-pagination>
                                    <p data-report-summary></p>
                                    <nav aria-label="Paginação do relatório">
                                        <button type="button" data-report-prev aria-label="Página anterior"><i class="ph ph-caret-left" aria-hidden="true"></i> Anterior</button>
                                        <span data-report-page aria-live="polite"></span>
                                        <button type="button" data-report-next aria-label="Próxima página">Próximo <i class="ph ph-caret-right" aria-hidden="true"></i></button>
                                    </nav>
                                </footer>
                            </div>
                        </section>
                    @endforeach
                </x-tabs.tabs>
            </div>
        </div>
        <p class="reports-page__note">Indicadores consolidados das movimentações e notas fiscais.</p>
    </section>

    <div class="reports-dialog-layer" data-dialog-layer="report-indicator-details" role="presentation" hidden>
        <x-dialogs.details-dialog id="report-indicator-dialog" title="Composição do indicador" description="Registros que compõem este indicador." variant="indicator" :columns="$reportSections['overview']['columns']" :rows="$reportSections['overview']['rows']" :status-variants="$reportSections['overview']['statusVariants']" />
    </div>
    <div class="reports-dialog-layer" data-dialog-layer="report-record-details" role="presentation" hidden>
        <x-dialogs.details-dialog id="report-record-dialog" title="Detalhes do registro" description="Informações completas do registro selecionado." :details="[['label' => 'Registro', 'value' => 'Selecione uma linha da tabela.']]" />
    </div>
</x-layouts.app>
