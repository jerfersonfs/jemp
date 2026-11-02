<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determina se o utilizador tem permissão para fazer este pedido.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação que os dados do produto têm de cumprir.
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'in:PBR,Descartável,Não Standard'],
            'product_name' => ['required', 'string', 'max:150'],
            'material' => ['required', 'string', 'max:100'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'length_mm' => ['required', 'integer', 'min:1'],
            'width_mm' => ['required', 'integer', 'min:1'],
        ];
    }
}
