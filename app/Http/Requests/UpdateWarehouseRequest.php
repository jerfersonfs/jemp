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
                Rule::unique('warehouses', 'warehouse_name')->ignore($this->route('warehouse'), 'warehouse_id')
            ],
            'warehouse_description' => ['required', 'string'],
        ];
    }
}
