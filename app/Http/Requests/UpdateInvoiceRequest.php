<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $imported = $this->route('invoice');
        if ($imported instanceof Invoice && $imported->access_key !== null) {
            return;
        }
        if ($this->exists('issuer_document')) {
            $this->merge(['issuer_document' => preg_replace('/\D/', '', (string) $this->input('issuer_document'))]);
        }
        $invoice = $this->route('invoice');
        if (! $this->filled('issue_date') && $invoice instanceof Invoice) {
            $this->merge(['issue_date' => $invoice->issue_date->toDateString()]);
        }

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
        $invoice = $this->route('invoice');
        if ($invoice instanceof Invoice && $invoice->access_key !== null) {
            $rules = ['payment_status' => ['required', 'in:A vencer,Paga']];
            foreach (['issuer_document', 'customer_id', 'invoice_id', 'invoice_number', 'invoice_serie', 'issue_date',
                'due_date', 'payment_method', 'delivery_driver', 'payment_terms_days', 'payment_date', 'amount',
                'access_key', 'stock_posted_at', 'created_at', 'updated_at', 'items'] as $field) {
                $rules[$field] = ['missing'];
            }

            return $rules;
        }

        return [
            'issuer_document' => ['sometimes', 'string', 'regex:/^(?:\d{14})?$/'],
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
            'amount' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        $invoice = $this->route('invoice');
        if ($invoice instanceof Invoice && $invoice->access_key !== null) {
            return [
                'missing' => 'Os dados da nota importada são somente para leitura. Altere apenas a situação do pagamento.',
                'payment_status.required' => 'Informe se a nota está paga ou não paga.',
                'payment_status.in' => 'Escolha paga ou não paga. A situação vencida é definida automaticamente pela data.',
            ];
        }

        return [];
    }
}
