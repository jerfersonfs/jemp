<x-layouts.app title="Visão geral · JEMP" page="home">
    <div class="page-head">
        <div>
            <h1>Visão geral</h1>
            <p class="sub">Indicadores da operação.</p>
        </div>
    </div>

    <section class="stats" aria-label="Indicadores da operação" data-count-up-group data-critical-stock-overview>
        <x-cards.stat-card label="Total Recebido" :value="$receivedTotal" variant="mint" :href="route('reports.index')" />
        <x-cards.stat-card label="Total a Receber" :value="$receivableTotal" :href="route('reports.index')" />
        <x-cards.stat-card label="Saldo vencido" :value="$overdueTotal" :href="route('reports.index')" />
        <x-cards.stat-card label="Estoque Crítico" :value="$criticalProductCount.' Produtos'" :href="route('products.index', ['estoque' => 'baixo'])" />
    </section>

    <section class="home-notice-grid" aria-label="Avisos da operação">
        <x-lists.notice-list title="Estoque Crítico" description="Menores saldos das condições habilitadas para venda" :items="$criticalStock" :count-label="count($criticalStock).' de '.$criticalPositionCount.' saldos'" />
        <x-lists.notice-list title="Contas a Receber" description="Títulos próximos do vencimento ou vencidos" :items="$receivables" />
    </section>
</x-layouts.app>
