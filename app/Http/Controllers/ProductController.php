<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View|JsonResponse|AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['inventory.warehouse', 'inventories.warehouse'])
            ->withSum('inventory', 'theoretical_balance')
            ->orderBy('sku')
            ->get();

        if ($request->expectsJson()) {
            return ProductResource::collection($products);
        }

        return view('products.products', [
            'page' => 'products',
            'products' => $products,
            'warehouses' => Warehouse::query()->orderBy('warehouse_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate($this->rules(includeInitialInventory: ! $request->expectsJson()));
        $this->assertSpecificationIsAvailable($data);

        try {
            $product = DB::transaction(function () use ($data): Product {
                $temporarySku = 'TMP-'.Str::random(8);
                $product = Product::query()->create([
                    ...$this->productData($data),
                    'sku' => $temporarySku,
                ]);
                $product->update([
                    'sku' => $this->generateSku(
                        $data['category'],
                        (int) $data['length_mm'],
                        (int) $data['width_mm'],
                        $product->product_id,
                    ),
                ]);

                if (isset($data['warehouse_id'])) {
                    DB::table('inventory')->insert([
                        'warehouse_id' => $data['warehouse_id'],
                        'product_id' => $product->product_id,
                        'product_condition' => $data['product_condition'],
                        'theoretical_balance' => $data['theoretical_balance'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $product->load('inventory.warehouse');
            });
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Falha ao cadastrar produto.'], 500);
            }

            return back()->with('error', 'Falha ao cadastrar produto.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Produto criado com sucesso.', 'data' => new ProductResource($product)], 201);
        }

        return redirect()->route('products.index')->with('status', 'Produto cadastrado com sucesso.');
    }

    public function update(Request $request, Product $produto): RedirectResponse|JsonResponse
    {
        $data = $request->validate($this->rules());
        $this->assertSpecificationIsAvailable($data, $produto);

        try {
            DB::transaction(function () use ($produto, $data): void {
                $lockedProduct = Product::query()->whereKey($produto->product_id)->lockForUpdate()->firstOrFail();
                $lockedProduct->update($this->productData($data));
            });
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Falha ao atualizar produto.'], 500);
            }

            return back()->with('error', 'Falha ao atualizar produto.');
        }

        $produto->refresh();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Produto atualizado com sucesso.', 'data' => new ProductResource($produto->load('inventory.warehouse'))]);
        }

        return redirect()->route('products.index')->with('status', 'Produto atualizado com sucesso.');
    }

    public function show(Product $produto): JsonResponse
    {
        return response()->json(new ProductResource($produto->load('inventory.warehouse')));
    }

    public function destroy(Product $produto): RedirectResponse|JsonResponse
    {
        if ($produto->stockMovements()->exists() || $produto->invoiceItems()->exists()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Produtos com histórico de movimentações ou notas fiscais não podem ser excluídos.'], 409);
            }

            return redirect()->route('products.index')->with('error', 'Produtos com histórico de movimentações ou notas fiscais não podem ser excluídos.');
        }

        try {
            DB::transaction(function () use ($produto): void {
                DB::table('inventory')->where('product_id', $produto->product_id)->delete();
                $produto->delete();
            });
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Falha ao excluir produto.'], 500);
            }

            return back()->with('error', 'Falha ao excluir produto.');
        }

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Produto excluído com sucesso.']);
        }

        return redirect()->route('products.index')->with('status', 'Produto excluído com sucesso.');
    }

    /** @param array<string, mixed> $data */
    private function productData(array $data): array
    {
        return [
            'category' => $data['category'],
            'product_name' => $data['product_name'],
            'material' => $data['material'],
            'cost_price' => $data['cost_price'],
            'length_mm' => $data['length_mm'],
            'width_mm' => $data['width_mm'],
        ];
    }

    private function generateSku(string $category, int $lengthMm, int $widthMm, int $sequence): string
    {
        $categoryCode = match ($category) {
            'PBR' => 'PBR',
            'Descartável' => 'DESC',
            'Não Standard' => 'NS',
        };

        return sprintf('%s-%dX%d-%03d', $categoryCode, $lengthMm, $widthMm, $sequence);
    }

    /** @param array<string, mixed> $data */
    private function assertSpecificationIsAvailable(array $data, ?Product $produto = null): void
    {
        $duplicateSpecification = Product::query()
            ->where('product_name', $data['product_name'])
            ->where('category', $data['category'])
            ->where('length_mm', $data['length_mm'])
            ->where('width_mm', $data['width_mm'])
            ->where('material', $data['material'])
            ->when($produto !== null, fn ($query) => $query->where('product_id', '!=', $produto->product_id))
            ->exists();

        if ($duplicateSpecification) {
            throw ValidationException::withMessages([
                'product_name' => 'Já existe um produto com essa especificação, categoria e material.',
            ]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(bool $includeInitialInventory = false): array
    {
        $rules = [
            'product_name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(['PBR', 'Descartável', 'Não Standard'])],
            'material' => ['required', 'string', 'max:100'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'length_mm' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'width_mm' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ];

        if ($includeInitialInventory) {
            $rules += [
                'warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
                'product_condition' => ['required', Rule::in(['Novo', 'Reforma', 'Descarte'])],
                'theoretical_balance' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ];
        }

        return $rules;
    }
}
