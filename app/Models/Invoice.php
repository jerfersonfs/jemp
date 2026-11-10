<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['invoice_number', 'invoice_serie', 'customer_id', 'issue_date', 'due_date', 'payment_method', 'delivery_driver', 'payment_terms_days', 'payment_status', 'payment_date', 'amount'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $primaryKey = 'invoice_id';

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

    public function calculatedAmount(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(fn (InvoiceItem $item): float => (int) $item->quantity * (float) $item->unit_price);
        }

        return (float) $this->items()->sum(DB::raw('quantity * unit_price'));
    }

    public function recalculateAmount(): void
    {
        $this->forceFill(['amount' => $this->calculatedAmount()])->saveQuietly();
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
