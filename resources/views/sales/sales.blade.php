<x-layouts.app title="Vendas · JEMP" page="sales">
    <div class="page-head sales-page__head">
        <div>
            <h1>Vendas</h1>
            <p>Consulte as notas fiscais registradas no sistema.</p>
        </div>
    </div>

    <section class="sales-card" aria-labelledby="sales-list-title">
        <header class="sales-card__header">
            <span class="sales-card__icon"><i class="ph ph-handshake" aria-hidden="true"></i></span>
            <div>
                <h2 id="sales-list-title">Vendas Registradas</h2>
                <p>Notas fiscais e itens existentes no banco de dados.</p>
            </div>
        </header>

        <section class="sales-items" aria-labelledby="sale-items-title">
            <header class="sales-items__header">
                <h3 id="sale-items-title">Notas Fiscais</h3>
            </header>
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
