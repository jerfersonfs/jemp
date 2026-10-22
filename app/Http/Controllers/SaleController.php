<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        return view('sales.sales', [
            'page' => 'sales',
            'customers' => Customer::query()->where('status', 'Ativo')->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->withSum('stocks', 'quantity')->orderBy('name')->get(),
            'sales' => Sale::query()->with('customer:id,name')->withCount('items')->latest('sold_at')->get(),
            'locations' => [
                'Barracão 1' => 'Barracão 1',
                'Barracão 2' => 'Barracão 2',
                'Barracão 3' => 'Barracão 3',
                'Depósito principal' => 'Depósito principal',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('status', 'Ativo')],
            'date' => ['required', 'date'],
            'invoice' => ['nullable', 'string', 'max:100'],
            'series' => ['nullable', 'string', 'max:30'],
            'payment' => ['nullable', 'in:Dinheiro,Cartão,Pix,Boleto'],
            'status' => ['required', 'in:Pendente,Concluída,Cancelada'],
            'warehouse' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.discount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);
        usort($data['items'], fn (array $left, array $right): int => (int) $left['product_id'] <=> (int) $right['product_id']);

        DB::transaction(function () use ($data): void {
            $subtotal = 0;
            $discountTotal = 0;
            $items = [];

            foreach ($data['items'] as $itemData) {
                $product = Product::query()->lockForUpdate()->findOrFail($itemData['product_id']);
                $quantity = (int) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $discount = (float) $itemData['discount'];
                $itemSubtotal = $quantity * $unitPrice;

                if ($discount > $itemSubtotal) {
                    throw ValidationException::withMessages(['items' => 'O desconto não pode ser maior que o subtotal do item.']);
                }

                $subtotal += $itemSubtotal;
                $discountTotal += $discount;
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total' => $itemSubtotal - $discount,
                ];
            }

            if ($data['status'] === 'Concluída') {
                foreach ($items as $item) {
                    $stock = ProductStock::query()
                        ->whereBelongsTo($item['product'])
                        ->where('location', $data['warehouse'])
                        ->lockForUpdate()
                        ->first();

                    if ($stock === null || $stock->quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => "Estoque insuficiente para {$item['product']->name} em {$data['warehouse']}.",
                        ]);
                    }

                    $stock->decrement('quantity', $item['quantity']);
                    $item['product']->decrement('quantity', $item['quantity']);
                }
            }

            $sale = Sale::query()->create([
                'code' => 'VEN-'.str()->ulid(),
                'customer_id' => $data['customer_id'],
                'sold_at' => $data['date'],
                'invoice' => $data['invoice'] ?? null,
                'series' => $data['series'] ?? null,
                'payment' => $data['payment'] ?? null,
                'status' => $data['status'],
                'warehouse' => $data['warehouse'],
                'subtotal' => $subtotal,
                'discount' => $discountTotal,
                'total' => $subtotal - $discountTotal,
                'user_id' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'product_code' => $item['product']->code,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                ]);

                if ($data['status'] === 'Concluída') {
                    \App\Models\Movement::query()->create([
                        'code' => 'MOV-'.str()->ulid(),
                        'product_id' => $item['product']->id,
                        'direction' => 'exit',
                        'type' => 'Venda',
                        'quantity' => $item['quantity'],
                        'occurred_at' => $data['date'],
                        'origin' => $data['warehouse'],
                        'recipient' => $sale->customer->name,
                        'reference' => $sale->code,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('sales.index')->with('status', 'Venda registrada com sucesso.');
    }
}
