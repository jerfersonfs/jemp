<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function entries(): View
    {
        return $this->page('entry', 'entrance.entrance');
    }

    public function exits(): View
    {
        return $this->page('exit', 'exits.exits');
    }

    public function transfers(): View
    {
        return $this->page('transfer', 'internal.internal');
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:Compra,Transferência,Devolução'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'justification' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->createMovement($data, 'entry');

        return redirect()->route('entrance.index')->with('status', 'Entrada registrada com sucesso.');
    }

    public function storeExit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:Consumo interno,Avaria,Venda,Ajuste de estoque'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'warehouse' => ['required', 'string', 'max:255'],
            'lot' => ['nullable', 'string', 'max:100'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['origin'] = $data['warehouse'];
        $this->createMovement($data, 'exit');

        return redirect()->route('exits.index')->with('status', 'Saída registrada com sucesso.');
    }

    public function storeTransfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'origin' => ['required', 'string', 'max:255', 'different:destination'],
            'destination' => ['required', 'string', 'max:255', 'different:origin'],
            'condition' => ['required', 'in:Novo,Bom,Revisado'],
        ]);
        $data['date'] = now();
        $data['type'] = 'Transferência';

        $this->createMovement($data, 'transfer');

        return redirect()->route('internal.index')->with('status', 'Transferência registrada com sucesso.');
    }

    private function page(string $direction, string $view): View
    {
        $movements = Movement::query()
            ->with('product:id,code,name')
            ->where('direction', $direction)
            ->latest('occurred_at')
            ->get();

        return view($view, [
            'page' => match ($direction) {
                'entry' => 'entrance',
                'exit' => 'exits',
                default => 'internal',
            },
            'products' => Product::query()->orderBy('name')->get(['id', 'code', 'name']),
            'movements' => $movements,
            'locations' => [
                'Barracão 1' => 'Barracão 1',
                'Barracão 2' => 'Barracão 2',
                'Barracão 3' => 'Barracão 3',
                'Depósito principal' => 'Depósito principal',
            ],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function createMovement(array $data, string $direction): void
    {
        DB::transaction(function () use ($data, $direction): void {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $quantity = (int) $data['quantity'];
            $origin = $data['origin'] ?? null;
            $destination = $data['destination'] ?? null;

            if ($direction === 'entry') {
                $this->changeStock($product, $destination, $quantity);
                $product->increment('quantity', $quantity);
            } elseif ($direction === 'exit') {
                $this->changeStock($product, $origin, -$quantity);
                $product->decrement('quantity', $quantity);
            } else {
                $this->changeStock($product, $origin, -$quantity);
                $this->changeStock($product, $destination, $quantity);
            }

            Movement::query()->create([
                'code' => 'MOV-'.str()->ulid(),
                'product_id' => $product->id,
                'direction' => $direction,
                'type' => $data['type'],
                'quantity' => $quantity,
                'occurred_at' => $data['date'] ?? now(),
                'origin' => $origin,
                'destination' => $destination,
                'lot' => $data['lot'] ?? null,
                'recipient' => $data['recipient'] ?? null,
                'reference' => $data['reference'] ?? null,
                'responsible' => $data['responsible'] ?? null,
                'condition' => $data['condition'] ?? null,
                'notes' => $data['notes'] ?? $data['justification'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });
    }

    private function changeStock(Product $product, ?string $location, int $change): void
    {
        if ($location === null || $location === '') {
            throw ValidationException::withMessages(['location' => 'Informe o local da movimentação.']);
        }

        $stock = ProductStock::query()
            ->whereBelongsTo($product)
            ->where('location', $location)
            ->lockForUpdate()
            ->first();

        if ($stock === null && $change < 0) {
            throw ValidationException::withMessages(['quantity' => 'Não há estoque deste produto no local selecionado.']);
        }

        if ($stock === null) {
            $product->stocks()->create(['location' => $location, 'quantity' => $change]);

            return;
        }

        if ($stock->quantity + $change < 0) {
            throw ValidationException::withMessages(['quantity' => 'A quantidade informada excede o estoque disponível neste local.']);
        }

        $stock->increment('quantity', $change);
    }
}
