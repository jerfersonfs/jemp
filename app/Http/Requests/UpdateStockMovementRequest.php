<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest // (Mude para UpdateStockMovementRequest no outro)
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin_warehouse_id' => ['nullable', 'integer'],
            'dest_warehouse_id' => ['nullable', 'integer'],
            'invoice_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'product_condition' => ['required', 'in:Novo,Reforma,Descarte'],
            'movement_type' => ['required', 'in:Entrada,Saída,Ajuste,Transferência'],
            'quantity' => ['required', 'integer', 'min:1'],
            'movement_date' => ['required', 'date'],
            'movement_justification' => ['nullable', 'string'],
            'is_fiscal' => ['required', 'boolean'],
        ];
    }
}
