<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

#[Fillable(['invoice_number', 'invoice_serie', 'customer_id', 'issue_date', 'delivery_driver', 'amount', 'issuer_document', 'access_key', 'stock_posted_at'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'invoice_id');
    }

    public function financialStatus(): string
    {
        return $this->is_cancelled ? 'Cancelada' : ($this->payment?->status ?? 'Sem pagamento cadastrado');
    }

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

        return (float) $this->items()->sum(InvoiceItem::totalExpression());
    }

    public function recalculateAmount(): void
    {
        $this->forceFill(['amount' => $this->calculatedAmount()])->saveQuietly();
    }

    public function itemsAreLocked(): bool
    {
        return $this->payment()->exists() || $this->is_cancelled || $this->access_key !== null || $this->stock_posted_at !== null || ($this->hasAttribute('has_stock_exit')
            ? (bool) $this->has_stock_exit
            : $this->stockMovements()->where('movement_type', 'Saída')->exists());
    }

    public function assertItemsEditable(): void
    {
        if ($this->itemsAreLocked()) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Os itens desta nota estão bloqueados para edição após a confirmação.',
            ]);
        }
    }

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'is_cancelled' => 'boolean',
            'amount' => 'decimal:2',
            'stock_posted_at' => 'datetime',
            'invoice_number' => 'integer',
            'invoice_serie' => 'integer',
        ];
    }
}
