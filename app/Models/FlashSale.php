<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $discount
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $ends_at_iso
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read mixed $url
 *
 * @method static Builder<static>|FlashSale newModelQuery()
 * @method static Builder<static>|FlashSale newQuery()
 * @method static Builder<static>|FlashSale query()
 * @method static Builder<static>|FlashSale running()
 * @method static Builder<static>|FlashSale whereCreatedAt($value)
 * @method static Builder<static>|FlashSale whereDescription($value)
 * @method static Builder<static>|FlashSale whereDiscount($value)
 * @method static Builder<static>|FlashSale whereEndsAt($value)
 * @method static Builder<static>|FlashSale whereId($value)
 * @method static Builder<static>|FlashSale whereIsActive($value)
 * @method static Builder<static>|FlashSale whereStartsAt($value)
 * @method static Builder<static>|FlashSale whereTitle($value)
 * @method static Builder<static>|FlashSale whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FlashSale extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_items')
            ->withPivot(['sale_price', 'qty_limit'])
            ->withTimestamps();
    }

    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    /** ISO timestamp the Alpine countdown ticks down to. */
    protected function endsAtIso(): Attribute
    {
        return Attribute::get(fn () => $this->ends_at?->toIso8601String());
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('deals'));
    }
}
