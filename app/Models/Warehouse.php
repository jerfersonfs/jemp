<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $primaryKey = 'warehouse_id';
    protected $fillable = [
        'warehouse_name',
        'warehouse_description'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventory', 'warehouse_id', 'product_id')
                    ->withPivot('product_condition', 'theoretical_balance')
                    ->withTimestamps();
    }
}
