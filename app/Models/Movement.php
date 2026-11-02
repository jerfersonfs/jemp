<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'product_id', 'direction', 'type', 'quantity', 'occurred_at', 'origin', 'destination', 'lot', 'recipient', 'reference', 'responsible', 'condition', 'notes', 'user_id'])]
class Movement extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
