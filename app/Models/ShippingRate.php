<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shipping_zone_id
 * @property string $name
 * @property string $method
 * @property numeric $min_weight_kg
 * @property numeric|null $max_weight_kg
 * @property int $base_rate
 * @property int $per_kg_rate
 * @property string $currency
 * @property int|null $transit_days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShippingZone $zone
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereBaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereMaxWeightKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereMinWeightKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate wherePerKgRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereShippingZoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereTransitDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ShippingRate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'min_weight_kg' => 'decimal:3',
            'max_weight_kg' => 'decimal:3',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
