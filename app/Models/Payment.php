<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['Dinheiro', 'PIX', 'Cartão de crédito', 'Cartão de débito', 'Boleto', 'Transferência bancária', 'Cheque', 'Outros'];

    protected $primaryKey = 'payment_id';

    public $timestamps = false;

    protected $fillable = ['installment_count', 'payment_terms_days', 'due_date', 'paid_at', 'payment_method'];

    protected $appends = ['status'];

    public static function today(): string
    {
        return now('America/Sao_Paulo')->toDateString();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function getStatusAttribute(): string
    {
        if ($this->paid_at !== null) {
            return 'Paga';
        }

        return $this->due_date < self::today() ? 'Vencida' : 'A vencer';
    }

    protected function casts(): array
    {
        return ['installment_count' => 'integer', 'payment_terms_days' => 'integer'];
    }
}
