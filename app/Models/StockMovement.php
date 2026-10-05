<?php<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $primaryKey = 'sm_id';
    protected $fillable = [
        'origin_warehouse_id',
        'dest_warehouse_id',
        'product_id',
        'user_id',
        'invoice_id',
        'product_condition',
        'movement_type',
        'quantity',
        'movement_date',
        'movement_justification',
        'is_fiscal',
    ];
}
