<?php

namespace App\Http\Resources;

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
            'issue_date' => $this->issue_date->format('d/m/Y'),
            'due_date' => $this->due_date->format('d/m/Y'),
            'payment_method' => (string) $this->payment_method,
            'delivery_driver' => (string) $this->delivery_driver,
            'payment_terms_days' => (int) $this->payment_terms_days,
            'payment_status' => (string) $this->payment_status,
            'payment_date' => $this->payment_date ->format('d/m/Y'),
        ];
    }
}
