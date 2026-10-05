<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';
    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'unit_price'
    ];

    public function invoice(){
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
