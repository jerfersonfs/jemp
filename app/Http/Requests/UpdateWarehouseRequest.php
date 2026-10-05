<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_name' => [
                'required',
                'string',
                'max:100',
                // O Laravel tira o "s" de "armazens" na rota, gerando o parâmetro "armazen"
                Rule::unique('warehouses', 'warehouse_name')->ignore($this->route('armazen'), 'warehouse_id')
            ],
            'warehouse_description' => ['required', 'string'],
        ];
    }
}
