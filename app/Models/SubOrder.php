<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $order_id
 * @property int $vendor_id
 * @property string $reference
 * @property string $status
 * @property int $subtotal
 * @property int $shipping_total
 * @property int $tax_total
 * @property int $total
 * @property int $commission_amount
 * @property int $vendor_payout_amount
 * @property string $payout_status
 * @property Carbon|null $escrow_released_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read Order $order
 * @property-read Collection<int, Shipment> $shipments
 * @property-read int|null $shipments_count
 * @property-read Collection<int, OrderStatusHistory> $statusHistory
 * @property-read int|null $status_history_count
 * @property-read mixed $total_label
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereCommissionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereEscrowReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder wherePayoutStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereShippingTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereTaxTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubOrder whereVendorPayoutAmount($value)
 *
 * @mixin \Eloquent
 */
class SubOrder extends Model
{
    use HasFactory;
    use LogsActivity;

    public const PAYOUT_PENDING = 'pending';

    public const PAYOUT_ELIGIBLE = 'eligible';

    public const PAYOUT_PAID = 'paid';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['escrow_released_at' => 'datetime'];
    }

    /** Fulfilment and payout state changes are auditable. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payout_status', 'escrow_released_at', 'vendor_payout_amount'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('sub_order');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    protected function totalLabel(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->total, $this->order?->currency ?? 'USD'));
    }
}
