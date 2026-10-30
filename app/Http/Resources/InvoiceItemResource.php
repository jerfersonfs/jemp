<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => (int) $this->id,
            'invoice_id' => (int) $this->invoice_id,
            'product_id' => (int) $this->product_id,
            'quantity' => (int) $this->quantity,
            'unit_price' => number_format((float) $this->unit_price, 2, ',', '.'),
            'total_price' => number_format((float) $this->total_price, 2, ',', '.'),
        ];
    }
}
