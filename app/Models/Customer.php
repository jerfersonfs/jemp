<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_name', 'customer_document', 'customer_contact', 'customer_email', 'customer_payment_terms'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $primaryKey = 'customer_id';

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'customer_id');
    }
}
