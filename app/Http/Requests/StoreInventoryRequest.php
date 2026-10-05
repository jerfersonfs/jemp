<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'product_condition' => ['required', 'in:Novo,Reforma,Descarte'],
            'theoretical_balance' => ['required', 'integer', 'min:0'],
        ];
    }
}
