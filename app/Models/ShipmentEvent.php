<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shipment_id
 * @property string $status
 * @property string|null $location
 * @property string|null $description
 * @property Carbon $happened_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Shipment $shipment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereHappenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereShipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipmentEvent whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ShipmentEvent extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['happened_at' => 'datetime'];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
