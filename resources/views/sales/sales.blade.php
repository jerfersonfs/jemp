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
                <p>Consulte as notas fiscais e os itens registrados.</p>
            </div>
        </header>

        <section class="sales-items" aria-labelledby="sale-items-title">
            <header class="sales-items__header">
                <h3 id="sale-items-title">Notas Fiscais</h3>
            </header>
            <div class="sales-table-wrap">
                <table class="sales-table">
                    <thead><tr><th>Número</th><th>Série</th><th>CNPJ emitente</th><th>Cliente</th><th>Emissão</th><th>Unidades</th><th>Situação</th><th>Total calculado</th></tr></thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice['number'] }}</td>
                                <td>{{ $invoice['series'] }}</td>
                                <td>{{ $invoice['issuer_document'] }}</td>
                                <td>{{ $invoice['customer'] }}</td>
                                <td>{{ $invoice['date'] }}</td>
                                <td>{{ $invoice['items'] }}</td>
                                <td>{{ $invoice['status'] }}</td>
                                <td>R$ {{ number_format($invoice['total'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8">Nenhuma nota fiscal encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <section class="sales-card" aria-labelledby="sales-period-report-title" style="margin-top: 1.25rem">
        <header class="sales-card__header">
            <span class="sales-card__icon"><i class="ph ph-chart-line-up" aria-hidden="true"></i></span>
            <div>
                <h2 id="sales-period-report-title">Vendas por produto no período</h2>
                <p>Resumo de vendas calculado para o período informado.</p>
            </div>
        </header>

        <form method="GET" action="{{ route('sales.index') }}" style="display:flex; flex-wrap:wrap; align-items:end; gap:.75rem; padding:1rem 1.125rem">
            <label>Data inicial <input type="date" name="start_date" value="{{ $startDate }}" required></label>
            <label>Data final <input type="date" name="end_date" value="{{ $endDate }}" required></label>
            <button class="btn btn-primary" type="submit">Consultar período</button>
        </form>

        @if ($salesReportError)
            <p role="alert" style="padding:0 1.125rem; color:var(--color-danger)">{{ $salesReportError }}</p>
        @else
            <div class="sales-table-wrap">
                <table class="sales-table">
                    <thead><tr><th>SKU</th><th>Produto</th><th>Notas</th><th>Unidades vendidas</th><th>Faturamento bruto</th><th>Saldo atual</th></tr></thead>
                    <tbody>
                        @forelse ($salesReport as $row)
                            <tr>
                                <td>{{ $row->sku }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ number_format((int) $row->invoice_count, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $row->units_sold, 0, ',', '.') }}</td>
                                <td>R$ {{ number_format((float) $row->gross_revenue, 2, ',', '.') }}</td>
                                <td>{{ number_format((int) $row->current_stock, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Nenhuma venda encontrada nesse período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
