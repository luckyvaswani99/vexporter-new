<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $type
 * @property numeric $value
 * @property string $scope
 * @property int|null $vendor_id
 * @property int|null $min_order
 * @property int|null $max_discount
 * @property int|null $usage_limit
 * @property int $used_count
 * @property int|null $per_user_limit
 * @property array<array-key, mixed>|null $applies_to
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Vendor|null $vendor
 *
 * @method static Builder<static>|Coupon newModelQuery()
 * @method static Builder<static>|Coupon newQuery()
 * @method static Builder<static>|Coupon query()
 * @method static Builder<static>|Coupon redeemable()
 * @method static Builder<static>|Coupon whereAppliesTo($value)
 * @method static Builder<static>|Coupon whereCode($value)
 * @method static Builder<static>|Coupon whereCreatedAt($value)
 * @method static Builder<static>|Coupon whereEndsAt($value)
 * @method static Builder<static>|Coupon whereId($value)
 * @method static Builder<static>|Coupon whereIsActive($value)
 * @method static Builder<static>|Coupon whereMaxDiscount($value)
 * @method static Builder<static>|Coupon whereMinOrder($value)
 * @method static Builder<static>|Coupon wherePerUserLimit($value)
 * @method static Builder<static>|Coupon whereScope($value)
 * @method static Builder<static>|Coupon whereStartsAt($value)
 * @method static Builder<static>|Coupon whereType($value)
 * @method static Builder<static>|Coupon whereUpdatedAt($value)
 * @method static Builder<static>|Coupon whereUsageLimit($value)
 * @method static Builder<static>|Coupon whereUsedCount($value)
 * @method static Builder<static>|Coupon whereValue($value)
 * @method static Builder<static>|Coupon whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class Coupon extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'applies_to' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'value' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeRedeemable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }
}
