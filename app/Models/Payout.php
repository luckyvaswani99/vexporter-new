<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $vendor_id
 * @property Carbon|null $period_start
 * @property Carbon|null $period_end
 * @property int $amount
 * @property string $currency
 * @property string $status
 * @property string|null $gateway
 * @property string|null $gateway_transfer_id
 * @property array<array-key, mixed>|null $sub_order_ids
 * @property Carbon|null $processed_at
 * @property string|null $failure_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereGatewayTransferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereSubOrderIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payout whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class Payout extends Model
{
    use HasFactory;
    use LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sub_order_ids' => 'array',
            'period_start' => 'date',
            'period_end' => 'date',
            'processed_at' => 'datetime',
        ];
    }

    /** Money leaving the platform is always logged. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'gateway', 'gateway_transfer_id', 'processed_at', 'failure_reason'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('payout');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
