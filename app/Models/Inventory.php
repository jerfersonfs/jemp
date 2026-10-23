<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['warehouse_id', 'product_id', 'product_condition', 'theoretical_balance'])]
class Inventory extends Model
{
    protected $table = 'inventory';

    protected $primaryKey = null;

    public $incrementing = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }

    protected function casts(): array
    {
        return ['theoretical_balance' => 'integer'];
    }
}
