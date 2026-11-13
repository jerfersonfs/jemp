<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            'customers' => Customer::query()->orderBy('customer_name')->get(['customer_id', 'customer_name', 'customer_document']),
            'products' => Product::query()->orderBy('product_name')->get(['product_id', 'product_name', 'sku', 'cost_price']),
            'warehouses' => Warehouse::query()->orderBy('warehouse_name')->get(['warehouse_id', 'warehouse_name']),
        ]);
    }

    /** @return Collection<int, array<string, int|float|string>> */
    private function invoiceRows(bool $forConsult = false): Collection
    {
        Invoice::refreshOverdueStatuses();

        return Invoice::query()
            ->with(['customer', 'items.product'])
            ->withExists(['stockMovements as has_stock_exit' => fn ($query) => $query->where('movement_type', 'Saída')])
            ->latest('issue_date')
            ->get()
            ->map(function (Invoice $invoice) use ($forConsult): array {
                $total = $invoice->calculatedAmount();
                $customerName = $invoice->customer?->customer_name ?? '—';

                if (! $forConsult) {
                    return [
                        'number' => $invoice->invoice_number,
                        'series' => $invoice->invoice_serie,
                        'issuer_document' => $invoice->issuer_document ?: 'Não informado',
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
                    'id' => (string) $item->item_id,
                    'product_id' => (string) $item->product_id,
                    'product' => $item->product?->product_name ?? 'Produto',
                    'sku' => $item->product?->sku ?? '—',
                    'external_code' => $item->external_code,
                    'external_description' => $item->external_description,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                    'unit_price_display' => 'R$ '.number_format((float) $item->unit_price, 2, ',', '.'),
                    'subtotal' => 'R$ '.number_format((int) $item->quantity * (float) $item->unit_price, 2, ',', '.'),
                ])->all();

                return [
                    'id' => $id,
                    'items_locked' => $invoice->itemsAreLocked(),
                    'issuer_document_raw' => $invoice->issuer_document,
                    'imported_xml' => $invoice->access_key !== null,
                    'issuer_document' => $invoice->issuer_document ?: 'Não informado',
                    'access_key' => $invoice->access_key,
                    'customer_id' => (string) $invoice->customer_id,
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
                    'payment_method' => $invoice->payment_method,
                    'payment_terms_days' => (string) $invoice->payment_terms_days,
                    'total' => $formattedTotal,
                    'status' => $invoice->payment_status,
                    'details_target' => 'invoice-details-'.$id,
                    'edit_target' => 'invoice-edit-'.$id,
                    'add_item_target' => 'invoice-add-item-'.$id,
                    'search_terms' => $invoice->invoice_number.' '.$invoice->invoice_serie.' '.$customerName.' '.($invoice->customer?->customer_document ?? ''),
                    'items' => $items,
                ];
            });
    }
}
