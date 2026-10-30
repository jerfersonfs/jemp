<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Lista todos os produtos (Read - Tabela)
     */
    public function index()
    {
        $products = Product::with('inventories.warehouse')->get();

        return ProductResource::collection($products);
    }

    /**
     * Guarda o novo produto na base de dados (Create)
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Produto criado com sucesso.',
            'data' => new ProductResource($product)
        ], 201);
    }

    /**
     * Mostra os detalhes de apenas UM produto específico
     */
    public function show(Product $product)
    {
        $product->load('inventories.warehouse');

        return new ProductResource($product);
    }

    /**
     * Atualiza o produto na base de dados (Update)
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Produto atualizado com sucesso.',
            'data' => new ProductResource($product)
        ], 200);
    }

    /**
     * Remove o produto da base de dados (Delete)
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Produto excluído com sucesso.'
        ], 200);
    }
}
