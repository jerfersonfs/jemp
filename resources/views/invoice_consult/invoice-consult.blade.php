<x-layouts.app title="Consulta NF-e · JEMP" page="invoice-consult">
    <div class="page-head sales-page__head">
        <div><h1>Consulta de Notas Fiscais</h1><p>Consulte os dados disponíveis nos registros de notas fiscais.</p></div>
    </div>

    <x-cards.panel class="sales-card sales-history" variant="mint" title="Notas fiscais" description="Número, série, cliente, emissão, itens e situação registrados no banco.">
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
