<?php

namespace App\Models;

use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['warehouse_id', 'product_id', 'product_condition', 'theoretical_balance'])]
class Inventory extends Model
{
    /** @use HasFactory<InventoryFactory> */
    use HasFactory;

    protected $table = 'inventory';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['warehouse_id', 'product_id', 'product_condition', 'theoretical_balance'];

    public function getRouteKey(): string
    {
        return implode('~', [
            $this->warehouse_id,
            $this->product_id,
            $this->product_condition,
        ]);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $parts = explode('~', (string) $value, 3);

        if (count($parts) !== 3 || ! ctype_digit($parts[0]) || ! ctype_digit($parts[1]) || $parts[2] === '') {
            return null;
        }

        return static::query()
            ->where('warehouse_id', $parts[0])
            ->where('product_id', $parts[1])
            ->where('product_condition', $parts[2])
            ->first();
    }

    public function delete(): ?bool
    {
        $this->mergeAttributesFromCachedCasts();

        if (! $this->exists) {
            return null;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        $this->touchOwners();
        $this->setKeysForSaveQuery($this->newModelQuery())->delete();
        $this->exists = false;
        $this->fireModelEvent('deleted', false);

        return true;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('warehouse_id', $this->getOriginal('warehouse_id'))
            ->where('product_id', $this->getOriginal('product_id'))
            ->where('product_condition', $this->getOriginal('product_condition'));

        return $query;
    }

    protected function casts(): array
    {
        return ['theoretical_balance' => 'integer'];
    }
}
