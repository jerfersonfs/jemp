<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';
    protected $fillable = [
        'customer_id',
        'invoice_number',
        'invoice_serie',
        'issue_date',
        'due_date',
        'payment_method',
        'delivery_driver',
        'payment_terms_days',
        'payment_status',
        'payment_date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'invoice_id', 'invoice_id');
    }
}
