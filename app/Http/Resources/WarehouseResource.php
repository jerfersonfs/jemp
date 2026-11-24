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
            'warehouse_name' => (string) $this->warehouse_name,
            'warehouse_description' => (string) $this->warehouse_description,
            'warehouse_situation' => $this->warehouse_situation,
            'warehouse_request_reason' => $this->warehouse_request_reason,
            'warehouse_cancelation_reason' => $this->warehouse_cancelation_reason,
            'requested_by' => $this->requested_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
