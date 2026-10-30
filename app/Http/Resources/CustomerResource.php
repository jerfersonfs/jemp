<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id' => (int) $this->customer_id,
            'customer_name' => ucwords(strtolower($this->customer_name)),
            'customer_document' => (string) $this->customer_document,
            'customer_contact' => $this->customer_contact ?? 'Não informado',
            'customer_email' => $this->customer_email ?? 'Não informado',
            'default_payment_terms' => (int) $this->default_payment_terms,
        ];
    }
}
