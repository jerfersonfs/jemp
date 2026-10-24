<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
     public $timestamps = false;

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

     protected function casts(): array
        {
            return [
                'invoice_number' => 'integer',
                'invoice_serie' => 'integer',
                'issue_date' => 'date',
                'due_date' => 'date',
                'payment_date' => 'date',
                'payment_terms_days' => 'integer',
                'amount' => 'decimal:2',
            ];
        }
}
