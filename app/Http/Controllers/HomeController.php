<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        Invoice::refreshOverdueStatuses();
        $received = (float) DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.invoice_id', '=', 'ii.invoice_id')
            ->where('i.payment_status', 'Paga')
            ->sum(DB::raw('ii.quantity * ii.unit_price'));

        $receivableQuery = Invoice::query()
            ->with('customer')
            ->whereIn('payment_status', ['A vencer', 'Vencida']);

        $receivableTotal = (float) DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.invoice_id', '=', 'ii.invoice_id')
            ->whereIn('i.payment_status', ['A vencer', 'Vencida'])
            ->sum(DB::raw('ii.quantity * ii.unit_price'));
        $receivables = $receivableQuery
            ->with('items')
            ->orderByRaw("CASE WHEN payment_status = 'Vencida' THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn (Invoice $invoice): array => [
                'title' => $invoice->customer?->customer_name ?? 'Cliente não identificado',
                'reference' => 'NF-e '.str_pad((string) $invoice->invoice_number, 4, '0', STR_PAD_LEFT),
                'description' => 'Vencimento: '.$invoice->due_date->format('d/m/Y'),
                'details' => [['label' => 'Valor', 'value' => 'R$ '.number_format($invoice->calculatedAmount(), 2, ',', '.')]],
                'status' => $invoice->payment_status === 'Vencida' ? 'Vencida' : 'A vencer',
                'variant' => $invoice->payment_status === 'Vencida' ? 'danger' : 'warning',
            ])->all();

        $criticalQuery = DB::table('inventory as i')
            ->join('products as p', 'p.product_id', '=', 'i.product_id')
            ->join('warehouses as w', 'w.warehouse_id', '=', 'i.warehouse_id')
            ->where('i.theoretical_balance', '<=', 20);

        $criticalProducts = (clone $criticalQuery)->distinct()->count('i.product_id');
        $criticalStock = $criticalQuery
            ->orderBy('i.theoretical_balance')
            ->orderBy('p.product_name')
            ->limit(5)
            ->get([
                'p.product_name', 'p.sku', 'p.category', 'w.warehouse_name',
                'i.product_condition', 'i.theoretical_balance',
            ])
            ->map(fn (object $stock): array => [
                'title' => $stock->product_name,
                'reference' => 'SKU '.$stock->sku,
                'description' => 'Categoria: '.$stock->category.' · Armazém: '.$stock->warehouse_name.' · Condição: '.$stock->product_condition,
                'details' => [['label' => 'Estoque', 'value' => number_format((int) $stock->theoretical_balance, 0, ',', '.').' un.']],
                'status' => (int) $stock->theoretical_balance === 0 ? 'Zerado' : 'Crítico',
                'variant' => (int) $stock->theoretical_balance === 0 ? 'danger' : 'warning',
            ])->all();

        return view('jemp', [
            'page' => 'home',
            'receivedTotal' => 'R$ '.number_format($received, 2, ',', '.'),
            'receivableTotal' => 'R$ '.number_format($receivableTotal, 2, ',', '.'),
            'invoiceCount' => Invoice::query()->where('payment_status', '<>', 'Cancelada')->count(),
            'criticalProductCount' => $criticalProducts,
            'criticalStock' => $criticalStock,
            'receivables' => $receivables,
        ]);
    }
}
