<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            // Garante que não há 2 clientes com o mesmo CPF/CNPJ
            'customer_document' => ['required', 'string', 'max:255', 'unique:customers,customer_document'],
            'customer_contact' => ['required', 'string', 'max:20'],
            // Valida a regra de integridade >= 0
            'default_payment_terms' => ['required', 'integer', 'min:0'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
