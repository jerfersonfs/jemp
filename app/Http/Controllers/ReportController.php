<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\StockMovement;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    // Filtro 1: Faturas a vencer nos próximos X dias (padrão: 7)
    public function upcomingInvoices(Request $request): JsonResponse
    {
        $days = $request->query('days', 7);

        $invoices = Invoice::with('customer')
        ->where('payment_status', 'pendente')
        ->whereBetween('due_date', [now(), now()->addDays($days)])
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json(['data' => $invoices]);
    }

    // Filtro 2: Extrato de movimentações filtrado
    public function movementExtract(Request $request): JsonResponse
    {
        $query = StockMovement::with(['product', 'originWarehouse', 'destinationWarehouse', 'user']);

        if ($request->has('start_date')) {
            $query->whereDate('movement_date', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('movement_date', '<=', $request->query('end_date'));
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        return response()->json(['data' => $query->latest('movement_date')->get()]);
    }

    // Filtro 3: Consolidação do valor financeiro do inventário
    public function inventoryValuation(): JsonResponse
    {
        $valuation = Inventory::with('product')
        ->get()
        ->map(function ($item) {
            // Previne erros caso o produto tenha sido apagado da base de dados
            if (!$item->product) {
                return null;
            }

            $costPrice = (float) $item->product->cost_price;
            $quantity = (int) $item->theoretical_balance;

            return [
                'product_name' => $item->product->product_name,
                'warehouse_id' => $item->warehouse_id,
                'quantity' => $quantity,
                'total_value' => $quantity * $costPrice
            ];
        })
        ->filter(); // Remove os nulos

        return response()->json([
            'total_global' => $valuation->sum('total_value'),
                                'items' => $valuation->values()
        ]);
    }
}
