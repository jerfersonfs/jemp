<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'product_id';
    
    protected $fillable = [
        'sku',
        'category',
        'product_name',
        'material',
        'cost_price',
        'length_mm',
        'width_mm',
    ];
}
