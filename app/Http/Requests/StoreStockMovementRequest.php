<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'origin_warehouse_id' => 'nullable|integer',
            'dest_warehouse_id'   => 'nullable|integer',
            'product_id'          => 'required|integer',
            'user_id'             => 'required|integer',
            'product_condition'   => 'required|string',
            'movement_type'       => 'required|string',
            'quantity'            => 'required|integer|min:1',
            'movement_date'       => 'required|date',
            'is_fiscal'           => 'required|boolean',
            'movement_justification' => 'nullable|string',
            'invoice_id'          => 'nullable|integer'
        ];
    }
}
