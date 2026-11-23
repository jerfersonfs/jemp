<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StockMovement;
use App\SalePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function upcomingInvoices(Request $request): JsonResponse
    {
        $today = Payment::today();
        $days = max(0, min(365, (int) $request->query('days', 7)));
        $invoices = Invoice::with(['customer', 'payment'])->join('payments', 'payments.invoice_id', '=', 'invoices.invoice_id')
            ->where('invoices.is_cancelled', false)->whereNull('payments.paid_at')
            ->whereBetween('payments.due_date', [$today, Carbon::parse($today)->addDays($days)->toDateString()])
            ->orderBy('payments.due_date')->orderBy('invoices.invoice_id')->get(['invoices.*', 'payments.due_date']);

        return response()->json(['data' => $invoices]);
    }

    public function movementExtract(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'product_id' => ['nullable', 'integer', 'exists:products,product_id'],
        ]);
        $query = StockMovement::with(['product', 'originWarehouse', 'destinationWarehouse', 'user']);
        if (! empty($filters['start_date'])) {
            $query->whereDate('movement_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('movement_date', '<=', $filters['end_date']);
        }
        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return response()->json(['data' => $query->latest('movement_date')->get()]);
    }

    public function inventoryValuation(): JsonResponse
    {
        $items = Inventory::with('product')->get()
            ->filter(fn ($item) => $item->product !== null)
            ->map(fn ($item) => [
                'product_name' => $item->product->product_name,
                'warehouse_id' => $item->warehouse_id,
                'quantity' => (int) $item->theoretical_balance,
                'total_value' => (int) $item->theoretical_balance * (float) $item->product->cost_price,
            ])->values();

        return response()->json(['total_global' => $items->sum('total_value'), 'items' => $items]);
    }

    public function index(): View
    {
        $summary = DB::table('vw_visao_geral')->first() ?? (object) [
            'product_count' => 0,
            'total_stock' => 0,
            'low_stock_products' => 0,
            'movements_in_period' => 0,
            'invoice_count' => 0,
        ];

        $inventory = DB::table('vw_estoque_atual')
            ->orderBy('product_name')
            ->orderBy('warehouse_name')
            ->get();
        $invoices = Invoice::with(['customer', 'payment'])->orderByDesc('issue_date')->orderByDesc('invoice_id')->get();
        $movementDocuments = DB::table('vw_movimentacoes')
            ->get(['sm_id', 'associated_document'])
            ->keyBy('sm_id');
        $movementHistory = DB::table('vw_historico_movimentacoes')
            ->orderByDesc('movement_date')
            ->orderByDesc('sm_id')
            ->get();
        $audit = DB::table('vw_auditoria')
            ->orderByDesc('occurred_at')
            ->orderByDesc('record_id')
            ->get();
        $productSales = DB::table('vw_vendas_por_produto_90_dias')
            ->orderByDesc('units_sold_last_90_days')
            ->orderBy('product_name')
            ->get();

        $inventoryRows = $inventory
            ->map(fn (object $row): array => $this->inventoryRow($row))
            ->all();
        $lowInventoryRows = collect($inventoryRows)->whereIn('preset', ['low', 'zero'])->values()->all();
        $invoiceRows = $invoices
            ->map(fn (object $invoice): array => $this->invoiceRow($invoice))
            ->all();
        $overdueInvoiceRows = collect($invoiceRows)->where('preset', 'overdue')->values()->all();
        $productSalesRows = $productSales->map(function (object $row): array {
            $unitsSold = (int) $row->units_sold_last_90_days;
            $revenue = (float) $row->revenue_last_90_days;
            $currentStock = (int) $row->current_stock;

            return [
                'product_id' => $row->product_id,
                'code' => $row->sku,
                'product' => $row->product_name,
                'category' => $row->category,
                'units_sold' => number_format($unitsSold, 0, ',', '.').' un.',
                'raw_units_sold' => $unitsSold,
                'revenue' => 'R$ '.number_format($revenue, 2, ',', '.'),
                'raw_revenue' => $revenue,
                'current_stock' => number_format($currentStock, 0, ',', '.').' un.',
                'raw_current_stock' => $currentStock,
                'last_sale' => $this->formatDate($row->last_sale_date, 'd/m/Y'),
                'preset' => $unitsSold > 0 ? 'sold' : 'unsold',
            ];
        })->all();
        $mapMovement = function (object $movement) use ($movementDocuments): array {
            $row = $this->movementRow($movement);
            $row['document'] = $movementDocuments->get($movement->sm_id)?->associated_document ?? '—';

            return $row;
        };
        $movementRows = $movementHistory->map($mapMovement)->all();
        $movementMonthRows = $movementHistory
            ->filter(fn (object $movement): bool => Carbon::parse($movement->movement_date)->isCurrentMonth())
            ->map($mapMovement)
            ->all();
        $auditRows = $audit->map(fn (object $event): array => [
            'id' => (string) $event->record_id,
            'user' => $event->username,
            'action' => $event->action,
            'module' => $event->module,
            'date' => $this->formatDate($event->occurred_at, 'd/m/Y H:i'),
            'description' => $event->description,
            'preset' => $this->movementPreset($event->movement_type),
        ])->all();

        $invoiceCollection = collect($invoiceRows);
        $movementCollection = collect($movementRows);
        $auditCollection = collect($auditRows);
        $inventoryCollection = collect($inventoryRows);
        $productSalesCollection = collect($productSalesRows);
        $totalInvoiceValue = $invoiceCollection->sum('raw_total');
        $soldProducts = $productSalesCollection->where('preset', 'sold')->values();
        $unsoldProducts = $productSalesCollection->where('preset', 'unsold')->values();
        $totalUnitsSold = $productSalesCollection->sum('raw_units_sold');
        $totalSalesRevenue = $productSalesCollection->sum('raw_revenue');
        $totalProductStock = $productSalesCollection->sum('raw_current_stock');
        $lowStockProductCount = collect($lowInventoryRows)->pluck('product_id')->unique()->count();
        $entryQuantity = $movementCollection->where('preset', 'entry')->sum('raw_quantity');
        $exitQuantity = $movementCollection->where('preset', 'exit')->sum('raw_quantity');
        $transferQuantity = $movementCollection->where('preset', 'internal')->sum('raw_quantity');
        $warehouseCount = $inventoryCollection->pluck('warehouse')->filter()->unique()->count();
        $responsibleCount = $movementCollection->pluck('responsible')->filter()->unique()->count();

        $reportSections = [
            'overview' => [
                'label' => 'Visão Geral',
                'heading' => 'Visão geral da operação',
                'description' => 'Estoque por barracão, condição e situação atual.',
                'rows' => $inventoryRows,
                'presets' => [
                    ['value' => 'all', 'label' => 'Todos os produtos'],
                    ['value' => 'low', 'label' => 'Estoque baixo'],
                    ['value' => 'zero', 'label' => 'Zerados'],
                    ['value' => 'normal', 'label' => 'Normais'],
                ],
                'columns' => [
                    ['key' => 'code', 'label' => 'SKU'],
                    ['key' => 'product', 'label' => 'Produto'],
                    ['key' => 'category', 'label' => 'Categoria'],
                    ['key' => 'warehouse', 'label' => 'Barracão'],
                    ['key' => 'condition', 'label' => 'Condição'],
                    ['key' => 'quantity', 'label' => 'Qtd. atual'],
                    ['key' => 'unit', 'label' => 'Unidade'],
                    ['key' => 'status', 'label' => 'Situação', 'type' => 'status'],
                    ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver'],
                ],
                'statusVariants' => ['Normal' => 'success', 'Estoque baixo' => 'warning', 'Zerado' => 'danger'],
                'metrics' => [
                    ['label' => 'Produtos cadastrados', 'value' => number_format((int) $summary->product_count, 0, ',', '.'), 'source' => 'stock'],
                    ['label' => 'Unidades em estoque', 'value' => number_format((int) $summary->total_stock, 0, ',', '.'), 'source' => 'stock'],
                    ['label' => 'Produtos com estoque baixo', 'value' => $lowStockProductCount, 'source' => 'low'],
                    ['label' => 'Movimentações no mês', 'value' => (int) $summary->movements_in_period, 'source' => 'movement-month'],
                    ['label' => 'Notas fiscais', 'value' => (int) $summary->invoice_count, 'source' => 'invoices'],
                ],
            ],
            'invoices' => [
                'label' => 'Notas Fiscais',
                'heading' => 'Notas fiscais',
                'description' => 'Notas, clientes, vencimentos, pagamentos e valores calculados pelos itens.',
                'rows' => $invoiceRows,
                'presets' => [
                    ['value' => 'all', 'label' => 'Todas as notas'],
                    ['value' => 'paid', 'label' => 'Pagas'],
                    ['value' => 'due', 'label' => 'A vencer'],
                    ['value' => 'overdue', 'label' => 'Vencidas'],
                ],
                'columns' => [
                    ['key' => 'number', 'label' => 'Número'],
                    ['key' => 'series', 'label' => 'Série'],
                    ['key' => 'customer', 'label' => 'Cliente'],
                    ['key' => 'document', 'label' => 'Documento'],
                    ['key' => 'date', 'label' => 'Emissão'],
                    ['key' => 'due_date', 'label' => 'Vencimento'],
                    ['key' => 'paid_total', 'label' => 'Recebido'],
                    ['key' => 'open_total', 'label' => 'A receber'],
                    ['key' => 'overdue_total', 'label' => 'Vencido'],
                    ['key' => 'installments', 'label' => 'Parcelamento'],
                    ['key' => 'value', 'label' => 'Valor calculado'],
                    ['key' => 'status', 'label' => 'Situação', 'type' => 'status'],
                    ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver'],
                ],
                'statusVariants' => ['Paga' => 'success', 'A vencer' => 'warning', 'Vencida' => 'danger'],
                'metricRows' => [
                    'overdue' => $overdueInvoiceRows,
                ],
                'metrics' => [
                    ['label' => 'Notas fiscais', 'value' => (int) $summary->invoice_count, 'source' => 'invoices'],
                    ['label' => 'Valor total calculado', 'value' => 'R$ '.number_format($totalInvoiceValue, 2, ',', '.'), 'source' => 'invoices'],
                    ['label' => 'A vencer', 'value' => $invoiceCollection->where('preset', 'due')->count(), 'source' => 'invoice-due'],
                    ['label' => 'Vencidas', 'value' => count($overdueInvoiceRows), 'source' => 'invoice-overdue'],
                    ['label' => 'Pagas', 'value' => $invoiceCollection->where('preset', 'paid')->count(), 'source' => 'invoice-paid'],
                ],
            ],
            'product_sales' => [
                'label' => 'Vendas por produto',
                'heading' => 'Desempenho de vendas por produto',
                'description' => 'Vendas dos últimos 90 dias, saldo total entre barracões e condições e data da venda mais recente.',
                'rows' => $productSalesRows,
                'presets' => [
                    ['value' => 'all', 'label' => 'Todos os produtos'],
                    ['value' => 'sold', 'label' => 'Com vendas no período'],
                    ['value' => 'unsold', 'label' => 'Sem vendas no período'],
                ],
                'columns' => [
                    ['key' => 'code', 'label' => 'SKU'],
                    ['key' => 'product', 'label' => 'Produto'],
                    ['key' => 'category', 'label' => 'Categoria'],
                    ['key' => 'units_sold', 'label' => 'Unidades vendidas'],
                    ['key' => 'revenue', 'label' => 'Faturamento bruto'],
                    ['key' => 'current_stock', 'label' => 'Saldo atual'],
                    ['key' => 'last_sale', 'label' => 'Última venda'],
                    ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver'],
                ],
                'statusVariants' => [],
                'metricRows' => [
                    'sold' => $soldProducts->all(),
                    'unsold' => $unsoldProducts->all(),
                ],
                'metrics' => [
                    ['label' => 'Produtos com vendas', 'value' => $soldProducts->count(), 'source' => 'product-sales-sold'],
                    ['label' => 'Produtos sem vendas', 'value' => $unsoldProducts->count(), 'source' => 'product-sales-unsold'],
                    ['label' => 'Unidades vendidas', 'value' => number_format($totalUnitsSold, 0, ',', '.'), 'source' => 'product-sales-sold'],
                    ['label' => 'Faturamento bruto', 'value' => 'R$ '.number_format($totalSalesRevenue, 2, ',', '.'), 'source' => 'product-sales-sold'],
                    ['label' => 'Saldo total atual', 'value' => number_format($totalProductStock, 0, ',', '.'), 'source' => 'product-sales-all'],
                ],
            ],
            'stock' => [
                'label' => 'Estoque',
                'heading' => 'Estoque atual',
                'description' => 'Saldo e situação de cada produto por barracão e condição.',
                'rows' => $inventoryRows,
                'presets' => [
                    ['value' => 'all', 'label' => 'Todos os produtos'],
                    ['value' => 'low', 'label' => 'Estoque baixo'],
                    ['value' => 'zero', 'label' => 'Zerados'],
                    ['value' => 'normal', 'label' => 'Normais'],
                ],
                'columns' => [
                    ['key' => 'code', 'label' => 'SKU'],
                    ['key' => 'product', 'label' => 'Produto'],
                    ['key' => 'category', 'label' => 'Categoria'],
                    ['key' => 'warehouse', 'label' => 'Barracão'],
                    ['key' => 'condition', 'label' => 'Condição'],
                    ['key' => 'quantity', 'label' => 'Qtd. atual'],
                    ['key' => 'unit', 'label' => 'Unidade'],
                    ['key' => 'status', 'label' => 'Situação', 'type' => 'status'],
                    ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver'],
                ],
                'statusVariants' => ['Normal' => 'success', 'Estoque baixo' => 'warning', 'Zerado' => 'danger'],
                'metricRows' => [
                    'low' => $lowInventoryRows,
                ],
                'metrics' => [
                    ['label' => 'Produtos cadastrados', 'value' => number_format((int) $summary->product_count, 0, ',', '.'), 'source' => 'stock'],
                    ['label' => 'Unidades em estoque', 'value' => number_format((int) $summary->total_stock, 0, ',', '.'), 'source' => 'stock'],
                    ['label' => 'Produtos com estoque baixo', 'value' => $lowStockProductCount, 'source' => 'low'],
                    ['label' => 'Registros zerados', 'value' => $inventoryCollection->where('status', 'Zerado')->count(), 'source' => 'zero'],
                    ['label' => 'Barracões com saldo cadastrado', 'value' => $warehouseCount, 'source' => 'stock'],
                ],
            ],
            'movements' => [
                'label' => 'Movimentações',
                'heading' => 'Movimentações no período',
                'description' => 'Histórico real com usuário, barracões e documento associado quando informado.',
                'rows' => $movementRows,
                'presets' => [
                    ['value' => 'all', 'label' => 'Todas as movimentações'],
                    ['value' => 'entry', 'label' => 'Entradas'],
                    ['value' => 'exit', 'label' => 'Saídas'],
                    ['value' => 'internal', 'label' => 'Transferências'],
                ],
                'columns' => [
                    ['key' => 'id', 'label' => 'ID'],
                    ['key' => 'product', 'label' => 'Produto'],
                    ['key' => 'type', 'label' => 'Tipo'],
                    ['key' => 'quantity', 'label' => 'Quantidade'],
                    ['key' => 'date', 'label' => 'Data e hora'],
                    ['key' => 'responsible', 'label' => 'Responsável'],
                    ['key' => 'origin', 'label' => 'Origem'],
                    ['key' => 'destination', 'label' => 'Destino'],
                    ['key' => 'document', 'label' => 'Documento'],
                    ['key' => 'justification', 'label' => 'Justificativa'],
                    ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver'],
                ],
                'statusVariants' => [],
                'metricRows' => [
                    'month' => $movementMonthRows,
                ],
                'metrics' => [
                    ['label' => 'Movimentações registradas', 'value' => $movementCollection->count(), 'source' => 'movement-all'],
                    ['label' => 'Unidades recebidas', 'value' => number_format($entryQuantity, 0, ',', '.'), 'source' => 'movement-entry'],
                    ['label' => 'Unidades expedidas', 'value' => number_format($exitQuantity, 0, ',', '.'), 'source' => 'movement-exit'],
                    ['label' => 'Unidades transferidas', 'value' => number_format($transferQuantity, 0, ',', '.'), 'source' => 'movement-internal'],
                    ['label' => 'Usuários responsáveis', 'value' => $responsibleCount, 'source' => 'movement-all'],
                ],
            ],
            'audit' => [
                'label' => 'Auditoria',
                'heading' => 'Auditoria das movimentações de estoque',
                'description' => 'A trilha disponível registra movimentações de estoque; alterações de outros módulos e valores antes/depois não são armazenados pelo backend atual.',
                'rows' => $auditRows,
                'presets' => [
                    ['value' => 'all', 'label' => 'Todas as movimentações'],
                    ['value' => 'entry', 'label' => 'Entradas'],
                    ['value' => 'exit', 'label' => 'Saídas'],
                    ['value' => 'internal', 'label' => 'Transferências'],
                ],
                'columns' => [
                    ['key' => 'id', 'label' => 'ID'],
                    ['key' => 'user', 'label' => 'Usuário'],
                    ['key' => 'action', 'label' => 'Ação'],
                    ['key' => 'module', 'label' => 'Módulo'],
                    ['key' => 'date', 'label' => 'Data e hora'],
                    ['key' => 'description', 'label' => 'Descrição'],
                    ['key' => 'detailTarget', 'label' => 'Ações', 'type' => 'detail', 'target' => 'report-record-details', 'detailLabel' => 'Ver'],
                ],
                'statusVariants' => [],
                'metrics' => [
                    ['label' => 'Registros de estoque', 'value' => $auditCollection->count(), 'source' => 'audit-all'],
                    ['label' => 'Entradas', 'value' => $auditCollection->where('preset', 'entry')->count(), 'source' => 'audit-entry'],
                    ['label' => 'Saídas', 'value' => $auditCollection->where('preset', 'exit')->count(), 'source' => 'audit-exit'],
                    ['label' => 'Transferências', 'value' => $auditCollection->where('preset', 'internal')->count(), 'source' => 'audit-internal'],
                    ['label' => 'Usuários registrados', 'value' => $auditCollection->pluck('user')->filter()->unique()->count(), 'source' => 'audit-all'],
                ],
            ],
        ];

        foreach ($reportSections as $sectionKey => $section) {
            foreach ($section['rows'] as $rowIndex => $row) {
                $reportSections[$sectionKey]['rows'][$rowIndex]['detailTarget'] = 'report-record-details';
            }
        }

        return view('reports.reports', ['page' => 'reports', 'reportSections' => $reportSections]);
    }

    /** @return array{product_id: int|string, code: string, product: string, category: string, warehouse: string, condition: string, quantity: string, unit: string, status: string, preset: string} */
    private function inventoryRow(object $row): array
    {
        $quantity = (int) $row->theoretical_balance;
        $status = $row->stock_status;

        return [
            'product_id' => $row->product_id,
            'code' => $row->sku,
            'product' => $row->product_name,
            'category' => $row->category,
            'warehouse' => $row->warehouse_name,
            'condition' => $row->product_condition,
            'quantity' => number_format($quantity, 0, ',', '.'),
            'unit' => $row->unit ?? 'un.',
            'status' => $status,
            'preset' => match ($status) {
                'Estoque baixo' => 'low',
                'Zerado' => 'zero',
                'Fora de venda' => 'unavailable',
                default => 'normal',
            },
        ];
    }

    private function invoiceRow(Invoice $invoice): array
    {
        $payment = $invoice->payment;
        $status = $invoice->financialStatus();
        $value = 'R$ '.number_format((float) $invoice->amount, 2, ',', '.');

        return [
            'number' => (string) $invoice->invoice_number,
            'series' => (string) $invoice->invoice_serie,
            'customer' => $invoice->customer->customer_name,
            'document' => $invoice->customer->customer_document,
            'date' => $this->formatDate($invoice->issue_date, 'd/m/Y'),
            'due_date' => $this->formatDate($payment?->due_date, 'd/m/Y'),
            'paid_total' => $status === 'Paga' ? $value : 'R$ 0,00',
            'open_total' => in_array($status, ['Vencida', 'A vencer'], true) ? $value : 'R$ 0,00',
            'overdue_total' => $status === 'Vencida' ? $value : 'R$ 0,00',
            'installments' => $payment ? SalePayment::describe($invoice->amount, $payment->installment_count) : '—',
            'value' => $value,
            'raw_total' => (float) $invoice->amount,
            'status' => $status,
            'preset' => match ($status) {
                'Paga' => 'paid',
                'A vencer' => 'due',
                'Vencida' => 'overdue',
                default => 'all',
            },
        ];
    }

    /** @return array{id: string, product: string, type: string, quantity: string, raw_quantity: int, date: string, responsible: string, origin: string, destination: string, document: string, justification: string, preset: string} */
    private function movementRow(object $movement): array
    {
        return [
            'id' => (string) $movement->sm_id,
            'product' => trim($movement->sku.' — '.$movement->product_name),
            'type' => $movement->movement_type,
            'quantity' => number_format((int) $movement->quantity, 0, ',', '.').' un.',
            'raw_quantity' => (int) $movement->quantity,
            'date' => $this->formatDate($movement->movement_date, 'd/m/Y H:i'),
            'responsible' => $movement->username,
            'origin' => $movement->origin_warehouse_name ?? 'Externo',
            'destination' => $movement->destination_warehouse_name ?? 'Externo',
            'document' => $movement->associated_document ?? '—',
            'justification' => $movement->movement_justification ?? '—',
            'preset' => $this->movementPreset($movement->movement_type),
        ];
    }

    private function formatDate(?string $value, string $format): string
    {
        return $value === null ? '—' : Carbon::parse($value)->format($format);
    }

    private function movementPreset(string $type): string
    {
        return match ($type) {
            'Entrada' => 'entry',
            'Saída' => 'exit',
            'Transferência' => 'internal',
            default => 'all',
        };
    }
}
