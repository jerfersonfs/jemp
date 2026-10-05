<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $primaryKey = 'warehouse_id';
    protected $fillable = [
        'warehouse_name',
        'warehouse_description',
    ];
}
