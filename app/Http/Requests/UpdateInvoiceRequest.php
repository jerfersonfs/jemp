<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        $protected = ['invoice_id', 'due_date', 'payment_method', 'payment_terms_days', 'payment_status', 'payment_date',
            'amount', 'access_key', 'is_cancelled', 'stock_posted_at', 'created_at', 'updated_at', 'items', 'payment', 'payments', 'paid_at', 'installment_count'];
        $header = ['issuer_document', 'customer_id', 'invoice_number', 'invoice_serie', 'issue_date', 'delivery_driver'];
        if ($this->has('payment_status')) {
            return [
                'payment_status' => ['required', Rule::in(['Paga', 'A vencer'])],
                'payment_method' => ['nullable', 'required_if:payment_status,Paga', 'prohibited_unless:payment_status,Paga', Rule::in(Payment::METHODS)],
            ] + array_fill_keys(array_merge($protected, $header), ['missing']);
        }
        if ($this->route('invoice')->access_key !== null) {
            $protected = array_merge($protected, $header);
        }

        return array_fill_keys($protected, ['missing']) + [
            'issuer_document' => ['sometimes', 'nullable', 'digits:14'],
            'customer_id' => ['sometimes', 'required', 'exists:customers,customer_id'],
            'invoice_number' => ['sometimes', 'required', 'integer', 'min:1'],
            'invoice_serie' => ['sometimes', 'required', 'integer', 'min:1'],
            'issue_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'delivery_driver' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return ['missing' => 'Este campo está bloqueado. Registre o recebimento no pagamento da venda.'];
    }
}
