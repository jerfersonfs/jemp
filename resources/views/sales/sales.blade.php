<x-layouts.app title="Vendas · JEMP" page="sales">
    <div class="page-head sales-page__head">
        <div><h1>Vendas</h1><p>Consulte as notas fiscais e os itens vinculados.</p></div>
    </div>

    <x-cards.panel class="sales-card sales-history" variant="mint" title="Vendas registradas" description="Dados carregados das tabelas de notas fiscais e itens do banco.">
        <div class="sales-table-wrap">
            <table class="sales-table">
                <thead><tr><th>Número</th><th>Série</th><th>Cliente</th><th>Emissão</th><th>Unidades</th><th>Situação</th><th>Total calculado</th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice['number'] }}</td>
                            <td>{{ $invoice['series'] }}</td>
                            <td>{{ $invoice['customer'] }}</td>
                            <td>{{ $invoice['date'] }}</td>
                            <td>{{ $invoice['items'] }}</td>
                            <td>{{ $invoice['status'] }}</td>
                            <td>R$ {{ number_format($invoice['total'], 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="jemp-table__empty">Nenhuma nota fiscal encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-cards.panel>
</x-layouts.app>
