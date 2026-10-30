<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (
            $this->has('payment_status')
            && $this->input('payment_status') !== 'Paga'
            && ! $this->exists('payment_date')
        ) {
            $this->merge(['payment_date' => null]);
        }
    }

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
            'customer_id' => ['sometimes', 'required', 'exists:customers,customer_id'],
            'invoice_number' => ['sometimes', 'required', 'integer', 'min:1'],
            'invoice_serie' => ['sometimes', 'required', 'integer', 'min:1'],
            'issue_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date', 'after_or_equal:issue_date'],
            'payment_method' => ['sometimes', 'required', 'string', 'max:50'],
            'delivery_driver' => ['sometimes', 'nullable', 'string', 'max:100'],
            'payment_terms_days' => ['sometimes', 'required', 'integer', 'min:0'],
            'payment_status' => ['sometimes', 'required', 'in:A vencer,Vencida,Paga,Cancelada'],
            'payment_date' => ['sometimes', 'nullable', 'required_if:payment_status,Paga', 'prohibited_unless:payment_status,Paga', 'date'],
        ];
    }
}
