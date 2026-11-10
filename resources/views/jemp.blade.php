<x-layouts.app title="Visão geral · JEMP" page="home">
    <div class="page-head">
        <div>
            <h1>Visão geral</h1>
            <p class="sub">Indicadores da operação.</p>
        </div>
    </div>

    <section class="stats" aria-label="Indicadores da operação" data-count-up-group>
        <x-cards.stat-card label="Total Recebido" :value="$receivedTotal" variant="mint" :href="route('sales.index')" />
        <x-cards.stat-card label="Total a Receber" :value="$receivableTotal" :href="route('sales.index')" />
        <x-cards.stat-card label="Notas Emitidas" :value="$invoiceCount.' NF-e'" variant="mint" :href="route('invoice-consult.index')" />
        <x-cards.stat-card label="Estoque Crítico" :value="$criticalProductCount.' Produtos'" :href="route('products.index')" />
    </section>

    <section class="home-notice-grid" aria-label="Avisos da operação">
        <x-lists.notice-list title="Estoque Crítico" description="Produtos que precisam de atenção" :items="$criticalStock" />
        <x-lists.notice-list title="Contas a Receber" description="Títulos próximos do vencimento ou vencidos" :items="$receivables" />
    </section>
</x-layouts.app>
