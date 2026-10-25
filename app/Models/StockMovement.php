<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['origin_warehouse_id', 'dest_warehouse_id', 'product_id', 'product_condition', 'user_id', 'invoice_id', 'quantity', 'movement_type', 'movement_justification', 'movement_date', 'is_fiscal'])]
class StockMovement extends Model
{
    protected $primaryKey = 'sm_id';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id', 'warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'dest_warehouse_id', 'warehouse_id');
    }

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'quantity' => 'integer',
            'is_fiscal' => 'boolean',
        ];
    }
}
