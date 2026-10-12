<x-layouts.app title="Visão geral · JEMP" page="home">
    @php
        $criticalStock = [
            [
                'title' => 'Palete PBR',
                'reference' => 'Cód. 0001',
                'description' => 'Categoria: Palete · Localização: Barracão 1',
                'details' => [['label' => 'Estoque', 'value' => '1.920 un.']],
                'status' => 'Crítico',
                'variant' => 'warning',
            ],
            [
                'title' => 'Palete FP',
                'reference' => 'Cód. 0002',
                'description' => 'Categoria: Palete · Localização: Barracão 2',
                'details' => [['label' => 'Estoque', 'value' => '1 un.']],
                'status' => 'Zerado',
                'variant' => 'danger',
            ],
        ];

        $receivables = [
            [
                'title' => 'Palete PBR',
                'reference' => 'NF-e 0001',
                'description' => 'Cliente: Palete PBR',
                'details' => [['label' => 'Valor', 'value' => 'R$ 1.920,00']],
                'status' => 'A vencer',
                'variant' => 'warning',
            ],
            [
                'title' => 'Palete FP',
                'reference' => 'NF-e 0002',
                'description' => 'Cliente: Palete FP',
                'details' => [['label' => 'Valor', 'value' => 'R$ 1,00']],
                'status' => 'Vencida',
                'variant' => 'danger',
            ],
        ];
    @endphp

    <div class="page-head">
        <div>
            <h1>Visão geral</h1>
            <p class="sub">Indicadores da operação.</p>
        </div>
    </div>

    <section class="stats" aria-label="Indicadores da operação">
        <x-cards.stat-card label="Total Recebido" value="R$ 14.250,00" variant="mint" :href="route('sales.index')" />
        <x-cards.stat-card label="Total a Receber" value="R$ 38.900,00" :href="route('sales.index')" />
        <x-cards.stat-card label="Notas Emitidas" value="28 NF-e" variant="mint" :href="route('invoice-consult.index')" />
        <x-cards.stat-card label="Estoque Crítico" value="4 Produtos" :href="route('products.index')" />
    </section>

    <section class="home-notice-grid" aria-label="Avisos da operação">
        <x-lists.notice-list title="Estoque Crítico" description="Produtos que precisam de atenção" :items="$criticalStock" />
        <x-lists.notice-list title="Contas a Receber" description="Títulos próximos do vencimento ou vencidos" :items="$receivables" />
    </section>
</x-layouts.app>
