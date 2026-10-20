<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            // Ignora o documento do próprio cliente atual para não dar erro de "CNPJ já existe"
            'customer_document' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'customer_document')->ignore($this->route('cliente'), 'customer_id')
            ],
            'customer_contact' => ['required', 'string', 'max:20'],
            'default_payment_terms' => ['required', 'integer', 'min:0'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
