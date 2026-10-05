<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $primaryKey = 'customer_id';
    protected $fillable = [
        'customer_name',
        'customer_document',
        'customer_contact',
        'default_payment_terms',
        'customer_email',
    ];
}
