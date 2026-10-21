<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'category', 'type', 'width', 'length', 'quantity', 'location', 'material', 'wood'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'width' => 'decimal:2',
            'length' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }
}
