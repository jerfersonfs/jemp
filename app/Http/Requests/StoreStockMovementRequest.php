<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin_warehouse_id' => ['nullable', 'integer', Warehouse::activeIdRule()],
            'dest_warehouse_id' => ['nullable', 'integer', Warehouse::activeIdRule()],
            'invoice_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'product_condition' => ['required', 'in:Novo,Reforma,Descarte'],
            'movement_type' => ['required', 'in:Entrada,Transferência'],
            'quantity' => ['required', 'integer', 'min:1'],
            'movement_date' => ['required', 'date'],
            'movement_justification' => ['nullable', 'string'],
            'is_fiscal' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['movement_type.in' => 'As saídas são geradas automaticamente pela importação de NF-e de venda. Use Entradas ou Internas para as demais movimentações.'];
    }
}
