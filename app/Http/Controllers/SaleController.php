<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $endDate = $filters['end_date'] ?? now()->toDateString();
        $startDate = $filters['start_date'] ?? now()->subDays(90)->toDateString();
        $salesReport = [];
        $salesReportError = null;

        try {
            $salesReport = DB::select('CALL sp_relatorio_vendas_periodo(?, ?)', [$startDate, $endDate]);
        } catch (QueryException $exception) {
            report($exception);
            $salesReportError = 'Não foi possível carregar o relatório. Verifique se a stored procedure está aplicada no banco e se a conexão está ativa.';
        }

        return view('sales.sales', [
            'page' => 'sales',
            'invoices' => $this->invoiceRows(),
            'salesReport' => $salesReport,
            'salesReportError' => $salesReportError,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function invoices(): View
    {
        return view('invoice_consult.invoice-consult', [
            'page' => 'invoice-consult',
            'invoices' => $this->invoiceRows(forConsult: true),
            'warehouses' => Warehouse::query()->orderBy('warehouse_name')->get(['warehouse_id', 'warehouse_name']),
        ]);
    }

    /** @return Collection<int, array<string, int|float|string>> */
    private function invoiceRows(bool $forConsult = false): Collection
    {
        return Invoice::query()
            ->with(['customer', 'items.product'])
            ->latest('issue_date')
            ->get()
            ->map(function (Invoice $invoice) use ($forConsult): array {
                $total = $invoice->items->sum(fn ($item): float => (int) $item->quantity * (float) $item->unit_price);
                $customerName = $invoice->customer?->customer_name ?? '—';

                if (!$forConsult) {
                    return [
                        'number' => $invoice->invoice_number,
                        'series' => $invoice->invoice_serie,
                        'customer' => $customerName,
                        'date' => $invoice->issue_date->format('d/m/Y'),
                        'items' => $invoice->items->sum('quantity'),
                        'status' => $invoice->payment_status,
                        'total' => $total,
                    ];
                }

                $id = (string) $invoice->invoice_id;
                $formattedTotal = 'R$ '.number_format($total, 2, ',', '.');
                $items = $invoice->items->map(fn ($item): array => [
                    'label' => $item->product?->product_name ?? 'Produto',
                    'value' => (int) $item->quantity.' un. · R$ '.number_format((float) $item->unit_price, 2, ',', '.'),
                ])->all();

                return [
                    'id' => $id,
                    'number' => (string) $invoice->invoice_number,
                    'series' => (string) $invoice->invoice_serie,
                    'issuer' => $customerName,
                    'document' => $invoice->customer?->customer_document ?? '—',
                    'issue_date' => $invoice->issue_date->format('d/m/Y'),
                    'issue_date_iso' => $invoice->issue_date->format('Y-m-d'),
                    'due_date' => $invoice->due_date->format('d/m/Y'),
                    'due_date_iso' => $invoice->due_date->format('Y-m-d'),
                    'payment_date' => $invoice->payment_date?->format('d/m/Y') ?? '—',
                    'payment_date_iso' => $invoice->payment_date?->format('Y-m-d') ?? '',
                    'amount' => number_format($total, 2, '.', ''),
                    'total' => $formattedTotal,
                    'status' => $invoice->payment_status,
                    'details_target' => 'invoice-details-'.$id,
                    'edit_target' => 'invoice-edit-'.$id,
                    'search_terms' => $invoice->invoice_number.' '.$invoice->invoice_serie.' '.$customerName.' '.($invoice->customer?->customer_document ?? ''),
                    'items' => $items,
                ];
            });
    }
}
