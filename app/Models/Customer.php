<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'customer_name',
        'customer_document',
        'customer_contact',
        'customer_email',
        'default_payment_terms'
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'customer_id');
    }
}
