<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
            'warehouse_id' => ['sometimes', 'required', 'integer', Warehouse::activeIdRule()],
            'product_id' => ['sometimes', 'required', 'integer'],
            'product_condition' => ['sometimes', 'required', 'in:Novo,Reforma,Descarte'],
            'theoretical_balance' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
