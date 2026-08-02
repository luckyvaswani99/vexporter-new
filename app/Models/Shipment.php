<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sub_order_id
 * @property string|null $carrier
 * @property string|null $service
 * @property string|null $tracking_no
 * @property string|null $tracking_url
 * @property string $status
 * @property numeric|null $weight_kg
 * @property int $packages
 * @property string|null $incoterm
 * @property string|null $port_of_loading
 * @property string|null $port_of_discharge
 * @property string|null $container_no
 * @property string|null $bl_awb_no
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ShipmentEvent> $events
 * @property-read int|null $events_count
 * @property-read SubOrder $subOrder
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereBlAwbNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCarrier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereContainerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereIncoterm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment wherePackages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment wherePortOfDischarge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment wherePortOfLoading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereSubOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereTrackingNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereTrackingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereWeightKg($value)
 *
 * @mixin \Eloquent
 */
class Shipment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderByDesc('happened_at');
    }
}
