<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'warehouse_id' => (int) $this->warehouse_id,
            'product_id' => (int) $this->product_id,
            'product_condition' => $this->product_condition,
            'theoretical_balance' => (int) $this->theoretical_balance,
        ];
    }
}
