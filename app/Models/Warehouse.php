<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $primaryKey = 'warehouse_id';
    protected $fillable = [
        'warehouse_name',
        'warehouse_description'
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'warehouse_id', 'warehouse_id');
    }

    public function originMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'origin_warehouse_id', 'warehouse_id');
    }

    public function destinationMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'dest_warehouse_id', 'warehouse_id');
    }
}
