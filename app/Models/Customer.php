<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'customer_id');
    }
}
