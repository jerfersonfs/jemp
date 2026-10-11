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
            'origin_warehouse_id' => (int) $this->origin_warehouse_id,
            'dest_warehouse_id' => (int)$this->dest_warehouse_id,
            'product_id' => (int) $this->product_id,
            'product_condition' => $this->product_condition,
            'movement_type' => $this->movement_type,
            'quantity' => (int) $this->quantity,
            'movement_date' => $this->movement_date->format('d/m/Y H:i:s'),
            'movement_justification' => $this-> movement_justification,
            'is_fiscal' => $this->is_fiscal ? 'Fiscal' : 'Não Fiscal'
        ];
    }
}
