<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property int|null $vendor_id
 * @property int|null $order_id
 * @property int|null $sub_order_id
 * @property int|null $payout_id
 * @property int $debit
 * @property int $credit
 * @property string $currency
 * @property int $balance_after
 * @property string|null $reference
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry wherePayoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereSubOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LedgerEntry whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class LedgerEntry extends Model
{
    public const TYPE_SALE = 'sale';

    public const TYPE_COMMISSION = 'commission';

    public const TYPE_REFUND = 'refund';

    public const TYPE_PAYOUT = 'payout';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $guarded = [];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
