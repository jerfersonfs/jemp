<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable(['invoice_number', 'invoice_serie', 'customer_id', 'issue_date', 'due_date', 'payment_method', 'delivery_driver', 'payment_terms_days', 'payment_status', 'payment_date', 'amount', 'issuer_document', 'access_key', 'stock_posted_at'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected static function booted(): void
    {
        static::saving(function (Invoice $invoice): void {
            if ($invoice->due_date !== null && in_array($invoice->payment_status ?? 'A vencer', ['A vencer', 'Vencida'], true)) {
                $invoice->payment_status = self::unpaidStatus($invoice->due_date->toDateString());
                $invoice->payment_date = null;
            }
        });
    }

    public static function paymentDateToday(): string
    {
        return now('America/Sao_Paulo')->toDateString();
    }

    public static function unpaidStatus(string $dueDate): string
    {
        return $dueDate < self::paymentDateToday() ? 'Vencida' : 'A vencer';
    }

    public static function refreshOverdueStatuses(): int
    {
        $today = self::paymentDateToday();
        $overdue = self::query()->where('payment_status', 'A vencer')->where('due_date', '<', $today)
            ->update(['payment_status' => 'Vencida']);
        $upcoming = self::query()->where('payment_status', 'Vencida')->where('due_date', '>=', $today)
            ->update(['payment_status' => 'A vencer']);

        return $overdue + $upcoming;
    }

    public function changePaymentState(bool $paid): void
    {
        $paymentDate = $paid
            ? ($this->payment_status === 'Paga' && $this->payment_date !== null ? $this->payment_date->toDateString() : self::paymentDateToday())
            : null;
        $this->payment_status = $paid ? 'Paga' : self::unpaidStatus($this->due_date->toDateString());
        $this->payment_date = $paymentDate;
        $this->save();
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

        return (float) $this->items()->sum(DB::raw('quantity * unit_price'));
    }

    public function recalculateAmount(): void
    {
        $this->forceFill(['amount' => $this->calculatedAmount()])->saveQuietly();
    }

    public function itemsAreLocked(): bool
    {
        return $this->access_key !== null || $this->stock_posted_at !== null || ($this->hasAttribute('has_stock_exit')
            ? (bool) $this->has_stock_exit
            : $this->stockMovements()->where('movement_type', 'Saída')->exists());
    }

    public function assertItemsEditable(): void
    {
        if ($this->itemsAreLocked()) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Os itens desta nota estão bloqueados porque a saída do estoque já foi registrada.',
            ]);
        }
    }

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'stock_posted_at' => 'datetime',
            'invoice_number' => 'integer',
            'invoice_serie' => 'integer',
            'payment_terms_days' => 'integer',
        ];
    }
}
