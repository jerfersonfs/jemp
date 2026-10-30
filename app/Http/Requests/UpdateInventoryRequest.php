<?php

namespace App\Http\Requests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['sometimes', 'required', 'integer'],
            'product_id' => ['sometimes', 'required', 'integer'],
            'product_condition' => ['sometimes', 'required', 'in:Novo,Reforma,Descarte'],
            'theoretical_balance' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
