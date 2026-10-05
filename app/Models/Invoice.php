<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoices_id';
    protected $fillable = [
        'invoice_number',
        'invoice_serie',
        'customer_id',
        'issue_date',
        'due_date',
        'payment_method',
        'delivery_driver',
        'payment_terms_days',
        'payment_status',
        'payment_date'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
