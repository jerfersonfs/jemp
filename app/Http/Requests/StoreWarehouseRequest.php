<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'warehouse_name.required' => 'Informe o nome do armazém.',
            'warehouse_name.unique' => 'Já existe um armazém com esse nome.',
            'warehouse_description.required' => 'Descreva o armazém.',
            'warehouse_request_reason.required' => 'Informe o motivo da solicitação.',
            'missing' => 'Este campo é definido pelo sistema.',
        ];
    }

    public function rules(): array
    {
        return [
            'warehouse_name' => ['required', 'string', 'max:100', 'unique:warehouses,warehouse_name'],
            'warehouse_description' => ['required', 'string', 'max:4000'],
            'warehouse_request_reason' => ['required', 'string', 'max:4000'],
            'warehouse_situation' => ['missing'],
            'warehouse_cancelation_reason' => ['missing'],
            'requested_by' => ['missing'],
            'warehouse_id' => ['missing'],
        ];
    }
}
