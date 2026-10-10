<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_condition',
        'theoretical_balance',
    ];
}
