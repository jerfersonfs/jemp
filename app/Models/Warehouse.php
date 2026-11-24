<?php

namespace App\Models;

use Database\Factories\WarehouseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;

class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use HasFactory;

    protected $primaryKey = 'warehouse_id';

    public const ACTIVE = 'Ativo';

    public const REQUESTED = 'Solicitado';

    public const SITUATIONS = [self::ACTIVE, self::REQUESTED, 'Inativo', 'Recusado'];

    protected $fillable = ['warehouse_name', 'warehouse_description', 'warehouse_request_reason'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('warehouse_situation', self::ACTIVE);
    }

    public static function activeIdRule(): Exists
    {
        return Rule::exists(self::class, 'warehouse_id')->where('warehouse_situation', self::ACTIVE);
    }

    /** @param array<int|string, int|string|null> $fields Warehouse IDs for a stock operation inside its transaction. */
    public static function lockActive(array $fields): void
    {
        $ids = array_filter($fields, static fn ($id): bool => $id !== null);
        $active = self::query()->active()->whereKey(array_values($ids))->orderBy('warehouse_id')
            ->sharedLock()->pluck('warehouse_id')->all();

        foreach ($ids as $field => $id) {
            if (! in_array((int) $id, $active, true)) {
                throw ValidationException::withMessages([is_string($field) ? $field : 'warehouse_id' => 'Selecione um armazém ativo para esta operação.']);
            }
        }
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'warehouse_id', 'warehouse_id');
    }

    public function inventories(): HasMany
    {
        return $this->inventory();
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
