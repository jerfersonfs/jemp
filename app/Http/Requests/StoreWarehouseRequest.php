<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Garante o máximo de 100 chars e que o nome não se repete
            'warehouse_name' => ['required', 'string', 'max:100', 'unique:warehouses,warehouse_name'],
            'warehouse_description' => ['required', 'string'],
        ];
    }
}
