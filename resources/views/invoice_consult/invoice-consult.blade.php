<x-layouts.app title="Consulta NF-e · JEMP" page="invoice-consult">
    <div class="page-head sales-page__head">
        <div>
            <h1>Consulta de Notas Fiscais</h1>
            <p>Consulte os dados disponíveis nos registros de notas fiscais.</p>
        </div>
    </div>

    <section class="sales-card" aria-labelledby="invoice-list-title">
        <header class="sales-card__header">
            <span class="sales-card__icon"><i class="ph ph-receipt" aria-hidden="true"></i></span>
            <div>
                <h2 id="invoice-list-title">Notas Fiscais</h2>
                <p>Número, série, cliente, emissão, itens e situação registrados no banco.</p>
            </div>
        </header>

        <section class="sales-items" aria-labelledby="invoice-table-title">
            <header class="sales-items__header"><h3 id="invoice-table-title">Registros encontrados</h3></header>
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
                            <tr><td colspan="7">Nenhuma nota fiscal encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.app>
