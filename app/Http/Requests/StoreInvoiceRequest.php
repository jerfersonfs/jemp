<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer'],
            'invoice_number' => ['required', 'string', 'max:50'],
            'invoice_serie' => ['required', 'integer'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'delivery_driver' => ['required', 'string', 'max:100'],
            'payment_terms_days' => ['required', 'integer', 'min:0'],
            'payment_status' => ['required', 'in:A vencer,Vencida,Paga,Cancelada'],
            'payment_date' => ['nullable', 'date'],
        ];
    }
}
