<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
