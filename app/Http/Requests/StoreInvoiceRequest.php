<?php

namespace App\Http\Requests;

use App\SalePayment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('issue_date')) {
            $this->merge(['issue_date' => now()->toDateString()]);
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
        return SalePayment::rules() + [
            'customer_id' => ['required', 'exists:customers,customer_id'],
            'invoice_number' => ['required', 'integer', 'min:1'],
            'invoice_serie' => ['required', 'integer', 'min:1'],
            'issue_date' => ['sometimes', 'required', 'date'],
            'delivery_driver' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'list', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,product_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'amount' => ['prohibited'],
        ];
    }
}
