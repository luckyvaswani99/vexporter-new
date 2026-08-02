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

/**
 * @property int $id
 * @property string $reference
 * @property int $rfq_id
 * @property int $vendor_id
 * @property string $status
 * @property string $currency
 * @property int $subtotal
 * @property int $shipping
 * @property int $tax
 * @property int $total
 * @property string|null $incoterm
 * @property int|null $lead_time_days
 * @property Carbon|null $validity_until
 * @property string|null $payment_terms
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, QuoteItem> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, QuoteMessage> $messages
 * @property-read int|null $messages_count
 * @property-read Rfq $rfq
 * @property-read mixed $total_label
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereIncoterm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereLeadTimeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereRfqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereValidityUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class Quote extends Model
{
    use HasFactory;

    public const STATUS_SENT = 'sent';

    public const STATUS_REVISED = 'revised';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['validity_until' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(QuoteMessage::class);
    }

    public function isExpired(): bool
    {
        return $this->validity_until !== null && $this->validity_until->isPast();
    }

    protected function totalLabel(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->total, $this->currency));
    }
}
