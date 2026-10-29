<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Lista todos os produtos (Read - Tabela)
     */
    public function index()
    {
        // Busca todos os produtos na base de dados
        $products = Product::all();

        // Retorna para a vista do Frontend (que a equipa vai criar depois)
        // return view('products.index', compact('products'));

        // Por agora, vamos retornar como JSON para testarmos se funciona!
        return response()->json($products);
    }

    /**
     * Mostra o formulário para criar um novo produto
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Guarda o novo produto na base de dados (Create)
     * O 'StoreProductRequest' faz a validação de segurança antes de chegar aqui!
     */
    public function store(StoreProductRequest $request)
    {
        // Grava no MariaDB de forma segura
        Product::create($request->validated());

        // Em vez de JSON bruto, redireciona o utilizador para a página principal de produtos!
        return redirect()->route('produtos.index');
    }

    /**
     * Mostra os detalhes de apenas UM produto específico
     */
    public function show(Product $product)
    {
        // return view('products.show', compact('product'));
    }

    /**
     * Mostra o formulário para editar um produto existente
     */
    public function edit(Product $product)
    {
        // return view('products.edit', compact('product'));
    }

    /**
     * Atualiza o produto na base de dados (Update)
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return redirect()->route('produtos.index');
    }

    /**
     * Remove o produto da base de dados (Delete)
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('produtos.index');

    }
}
