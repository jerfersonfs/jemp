<?php

namespace App\Models;

use Database\Factories\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

#[Fillable(['invoice_id', 'product_id', 'quantity', 'unit_price', 'xml_line_number', 'external_code', 'external_description'])]
class InvoiceItem extends Model
{
    /** @use HasFactory<InvoiceItemFactory> */
    use HasFactory;

    protected $primaryKey = 'item_id';

    public static function totalExpression(): Expression
    {
        return DB::raw(in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
            ? 'fn_total_item_nota(quantity, unit_price)'
            : 'quantity * unit_price');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }
}
