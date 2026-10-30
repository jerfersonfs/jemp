<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'product_id' => $this->product_id,
        'sku' => $this->sku,
        'category' => $this->category,
        'product_name' => $this->product_name,
        'material' => $this->material,
        'cost_price' => number_format((float) $this->cost_price, 2, ',', '.'),
        'length_mm' => (int) $this->length_mm,
        'width_mm' => (int) $this->width_mm,
        ];
    }
}
