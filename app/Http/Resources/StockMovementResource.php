<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sm_id' => (int) $this->sm_id,
            'origin_warehouse_id' => $this->origin_warehouse_id ? (int) $this->origin_warehouse_id : null,
            'dest_warehouse_id' => $this->dest_warehouse_id ? (int) $this->dest_warehouse_id : null,
            'product_id' => (int) $this->product_id,
            'product_condition' => (string) $this->product_condition,
            'movement_type' => (string) $this->movement_type,
            'quantity' => (int) $this->quantity,
            'movement_date' => $this->movement_date->format('Y-m-d H:i:s'),
            'movement_justification' => $this->movement_justification ? (string) $this->movement_justification : null,
            'is_fiscal' => (bool) $this->is_fiscal,
        ];
    }
}
