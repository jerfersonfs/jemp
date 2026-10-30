<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $primaryKey = 'sm_id';

    protected $fillable = [
        'origin_warehouse_id',
        'dest_warehouse_id',
        'invoice_id',
        'product_id',
        'user_id',
        'product_condition',
        'movement_type',
        'quantity',
        'movement_date',
        'movement_justification',
        'is_fiscal'
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'is_fiscal' => 'boolean',
            'quantity' => 'integer',
        ];
    }

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }
}
