<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,customer_id'],
            'invoice_number' => ['required', 'integer', 'min:1'],
            'invoice_serie' => ['required', 'integer', 'min:1'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'delivery_driver' => ['nullable', 'string', 'max:100'],
            'payment_terms_days' => ['required', 'integer', 'min:0'],
            'payment_status' => ['sometimes', 'in:A vencer,Vencida,Paga,Cancelada'],
            'payment_date' => ['nullable', 'required_if:payment_status,Paga', 'prohibited_unless:payment_status,Paga', 'date'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0']
        ];
    }
}
