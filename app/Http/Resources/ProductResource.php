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
            'product_id' => (int) $this->product_id,
            'sku' => (string) $this->sku,
            'category' => (string) $this->category,
            'product_name' => (string) $this->product_name,
            'material' => (string) $this->material,
            'cost_price' => (float) $this->cost_price,
            'length_mm' => (int) $this->length_mm,
            'width_mm' => (int) $this->width_mm,
        ];
    }
}
