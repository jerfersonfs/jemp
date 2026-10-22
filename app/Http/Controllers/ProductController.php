<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.products', [
            'page' => 'products',
            'products' => Product::query()->with('stocks')->withSum('stocks', 'quantity')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        DB::transaction(function () use ($data): void {
            Product::query()->create($data);
        });

        return redirect()->route('products.index')->with('status', 'Produto cadastrado com sucesso.');
    }

    public function update(Request $request, Product $produto): RedirectResponse
    {
        $data = $request->validate([
            ...$this->rules($produto),
            'product_updated_at' => ['required', 'date'],
        ]);
        DB::transaction(function () use ($produto, $data): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($produto->id);
            if (! Carbon::parse($data['product_updated_at'])->equalTo($lockedProduct->updated_at)) {
                throw ValidationException::withMessages([
                    'quantity' => 'O produto foi atualizado depois que a página foi aberta. Atualize a lista e tente novamente.',
                ]);
            }

            $stocks = $lockedProduct->stocks()->lockForUpdate()->get();
            if ($stocks->count() > 1 && ((int) $data['quantity'] !== $lockedProduct->quantity || $data['location'] !== $lockedProduct->location)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Ajuste produtos distribuídos em mais de um local pela tela de movimentações.',
                ]);
            }

            $productData = array_diff_key($data, ['product_updated_at' => true]);
            $lockedProduct->update($productData);
            if ($stocks->count() === 1) {
                $stocks->first()->update(['location' => $data['location'], 'quantity' => $data['quantity']]);
            } else {
                $lockedProduct->stocks()->firstOrCreate(
                    ['location' => $data['location']],
                    ['quantity' => $data['quantity']],
                );
            }
            $lockedProduct->forceFill(['quantity' => $lockedProduct->stocks()->sum('quantity')])->save();
        });

        return redirect()->route('products.index')->with('status', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $produto): RedirectResponse
    {
        if ($produto->movements()->exists() || $produto->saleItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'Produtos com histórico de movimentações ou vendas não podem ser excluídos.');
        }

        $produto->delete();

        return redirect()->route('products.index')->with('status', 'Produto excluído com sucesso.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Product $produto = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('products', 'code')->ignore($produto)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['Descartável', 'Retornável'])],
            'type' => ['required', Rule::in(['PBR', 'FP'])],
            'width' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'length' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'quantity' => ['required', 'integer', 'min:0'],
            'location' => ['required', Rule::in(['Barracão 1', 'Barracão 2', 'Barracão 3', 'Depósito principal'])],
            'material' => ['required', 'string', 'max:100'],
            'wood' => ['required', 'string', 'max:100'],
        ];
    }
}
