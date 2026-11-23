<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $totals = DB::table('payments as p')->join('invoices as i', 'i.invoice_id', '=', 'p.invoice_id')
            ->where('i.is_cancelled', false)
            ->selectRaw('COALESCE(SUM(CASE WHEN p.paid_at IS NOT NULL THEN i.amount ELSE 0 END), 0) AS received,
                COALESCE(SUM(CASE WHEN p.paid_at IS NULL THEN i.amount ELSE 0 END), 0) AS receivable,
                COALESCE(SUM(CASE WHEN p.paid_at IS NULL AND p.due_date < ? THEN i.amount ELSE 0 END), 0) AS overdue', [Payment::today()])
            ->first();
        $receivables = Payment::with('invoice.customer')->whereNull('paid_at')
            ->whereHas('invoice', fn ($query) => $query->where('is_cancelled', false))
            ->orderBy('due_date')->orderBy('payment_id')->limit(5)->get()
            ->map(fn (Payment $payment): array => [
                'title' => $payment->invoice->customer->customer_name,
                'reference' => 'NF-e '.$payment->invoice->invoice_number.' · Série '.$payment->invoice->invoice_serie,
                'description' => 'Vencimento: '.Carbon::parse($payment->due_date)->format('d/m/Y'),
                'details' => [['label' => 'Valor da venda', 'value' => 'R$ '.number_format((float) $payment->invoice->amount, 2, ',', '.')]],
                'status' => $payment->status, 'variant' => $payment->status === 'Vencida' ? 'danger' : 'warning',
            ])->all();

        $criticalQuery = DB::table('vw_estoque_baixo');
        $criticalCounts = (clone $criticalQuery)->selectRaw('COUNT(DISTINCT product_id) AS products, COUNT(*) AS positions')->first();
        $criticalStock = $criticalQuery
            ->orderBy('theoretical_balance')
            ->orderBy('product_name')
            ->limit(5)
            ->get()
            ->map(fn (object $stock): array => [
                'title' => $stock->product_name.' · '.$stock->product_condition,
                'reference' => 'SKU '.$stock->sku,
                'description' => 'Armazém: '.$stock->warehouse_name,
                'details' => [['label' => 'Estoque', 'value' => number_format((int) $stock->theoretical_balance, 0, ',', '.').' un.']],
                'status' => (int) $stock->theoretical_balance === 0 ? 'Zerado' : 'Crítico',
                'variant' => (int) $stock->theoretical_balance === 0 ? 'danger' : 'warning',
            ])->all();

        return view('jemp', [
            'page' => 'home',
            'receivedTotal' => 'R$ '.number_format($totals->received, 2, ',', '.'),
            'receivableTotal' => 'R$ '.number_format($totals->receivable, 2, ',', '.'),
            'overdueTotal' => 'R$ '.number_format($totals->overdue, 2, ',', '.'),
            'invoiceCount' => Invoice::where('is_cancelled', false)->count(),
            'criticalProductCount' => (int) $criticalCounts->products,
            'criticalPositionCount' => (int) $criticalCounts->positions,
            'criticalStock' => $criticalStock,
            'receivables' => $receivables,
        ]);
    }
}
