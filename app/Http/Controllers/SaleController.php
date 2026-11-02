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
            'invoices' => $this->invoiceRows(),
        ]);
    }

    /** @return Collection<int, array<string, int|float|string>> */
    private function invoiceRows(): Collection
    {
        return Invoice::query()
            ->with(['customer', 'items'])
            ->latest('issue_date')
            ->get()
            ->map(fn (Invoice $invoice): array => [
                'number' => $invoice->invoice_number,
                'series' => $invoice->invoice_serie,
                'customer' => $invoice->customer->customer_name,
                'date' => $invoice->issue_date->format('d/m/Y'),
                'items' => $invoice->items->sum('quantity'),
                'status' => $invoice->payment_status,
                'total' => $invoice->items->sum(fn ($item): float => (int) $item->quantity * (float) $item->unit_price),
            ]);
    }
}
