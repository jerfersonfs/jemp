<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';
    protected $fillable = [
        'category',
        'product_name',
        'material',
        'cost_price',
        'length_mm',
        'width_mm'
    ];

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'inventory', 'product_id', 'warehouse_id')
                    ->withPivot('product_condition', 'theoretical_balance')
                    ->withTimestamps();
    }
}
