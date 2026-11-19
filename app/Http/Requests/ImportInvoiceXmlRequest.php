<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Validation\Rule;

class ImportInvoiceXmlRequest extends PreviewInvoiceXmlRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'customer_id' => ['required', 'integer', 'exists:customers,customer_id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
            'product_condition' => ['required', Rule::in(['Novo', 'Reforma', 'Descarte'])],
            'due_date' => ['sometimes', 'date_format:Y-m-d'],
            'payment_method' => ['exclude_unless:payment_status,Paga', 'required_if:payment_status,Paga', Rule::in(Invoice::PAYMENT_METHODS)],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'payment_status' => ['required', Rule::in(['A vencer', 'Paga'])],
            'payment_date' => ['nullable', 'required_if:payment_status,Paga', 'prohibited_unless:payment_status,Paga', 'date_format:Y-m-d'],
            'items' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'required' => 'Preencha :attribute.', 'exists' => 'O cadastro selecionado em :attribute não existe.',
            'items.prohibited' => 'Os produtos são identificados automaticamente pelo código do XML.',
            'payment_method.required_if' => 'Escolha a forma de pagamento para uma nota paga.',
            'payment_method.in' => 'Escolha uma das formas de pagamento disponíveis.',
            'payment_date.required_if' => 'Informe a data de pagamento para uma nota paga.',
            'payment_date.prohibited_unless' => 'A data de pagamento só deve ser informada para uma nota paga.',
            'payment_status.in' => 'Escolha paga ou não paga. A situação vencida é definida automaticamente pela data.',
            'date_format' => 'Informe uma data válida em :attribute.',
        ];
    }

    public function attributes(): array
    {
        return ['customer_id' => 'cliente', 'warehouse_id' => 'armazém de origem', 'product_condition' => 'condição',
            'due_date' => 'vencimento', 'payment_method' => 'forma de pagamento', 'payment_terms_days' => 'prazo de pagamento',
            'payment_status' => 'situação', 'payment_date' => 'data de pagamento'];
    }
}
