<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.products', [
            'page' => 'products',
            'products' => Product::query()->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::query()->create($request->validate($this->rules()));

        return redirect()->route('products.index')->with('status', 'Produto cadastrado com sucesso.');
    }

    public function update(Request $request, Product $produto): RedirectResponse
    {
        $produto->update($request->validate($this->rules($produto)));

        return redirect()->route('products.index')->with('status', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $produto): RedirectResponse
    {
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
            'location' => ['required', Rule::in(['Barracão 1', 'Barracão 2', 'Barracão 3'])],
            'material' => ['required', 'string', 'max:100'],
            'wood' => ['required', 'string', 'max:100'],
        ];
    }
}
