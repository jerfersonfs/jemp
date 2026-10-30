<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
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
            'warehouse_name' => $this->warehouse_name,
            'warehouse_description' => $this->warehouse_description,
        ];
    }
}
