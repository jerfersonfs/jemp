<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['invoice_number', 'invoice_serie', 'customer_id', 'issue_date', 'due_date', 'payment_method', 'delivery_driver', 'payment_terms_days', 'payment_status', 'payment_date'])]
class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'invoice_number' => 'integer',
            'invoice_serie' => 'integer',
            'payment_terms_days' => 'integer',
        ];
    }
}
