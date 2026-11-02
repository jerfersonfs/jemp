<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        return view('sales.sales', [
            'page' => 'sales',
            'invoices' => $this->invoiceRows(),
        ]);
    }

    public function invoices(): View
    {
        return view('invoice_consult.invoice-consult', [
            'page' => 'invoice-consult',
            'invoices' => $this->invoiceRows(forConsult: true),
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
