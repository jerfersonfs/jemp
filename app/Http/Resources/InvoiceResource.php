<?php

namespace App\Http\Resources;

use App\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'invoice_id' => (int) $this->invoice_id,
            'customer_id' => (int) $this->customer_id,
            'invoice_number' => (string) $this->invoice_number,
            'invoice_serie' => (int) $this->invoice_serie,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'delivery_driver' => (string) $this->delivery_driver,
            'amount' => $this->amount,
            'status' => $this->resource->financialStatus(),
            'payment_status' => $this->resource->financialStatus(),
            'due_date' => $this->payment?->due_date,
            'payment_method' => $this->payment?->payment_method,
            'payment_date' => $this->payment?->paid_at,
            'payment_terms_days' => $this->payment?->payment_terms_days,
            'payment' => $this->payment,
            'installments' => $this->payment ? SalePayment::breakdown($this->amount, $this->payment->installment_count) : [],
            'items' => $this->whenLoaded('items'),
        ];
    }
}
