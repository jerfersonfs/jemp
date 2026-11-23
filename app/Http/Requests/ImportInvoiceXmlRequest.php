<?php

namespace App\Http\Requests;

use App\SalePayment;
use Illuminate\Validation\Rule;

class ImportInvoiceXmlRequest extends PreviewInvoiceXmlRequest
{
    public function rules(): array
    {
        return parent::rules() + SalePayment::rules() + [
            'customer_id' => ['required', 'integer', 'exists:customers,customer_id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
            'product_condition' => ['required', Rule::in(['Novo', 'Reforma', 'Descarte'])],
            'items' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'required' => 'Preencha :attribute.', 'exists' => 'O cadastro selecionado em :attribute não existe.',
            'items.prohibited' => 'Os produtos são identificados automaticamente pelo código do XML.',
            'date_format' => 'Informe uma data válida em :attribute.',
        ];
    }

    public function attributes(): array
    {
        return ['customer_id' => 'cliente', 'warehouse_id' => 'armazém de origem', 'product_condition' => 'condição',
            'payment_terms_days' => 'prazo de pagamento', 'installment_count' => 'quantidade de parcelas',
            'payment_date' => 'data do pagamento', 'payment_method' => 'forma de pagamento', 'payment_status' => 'situação do pagamento'];
    }
}
