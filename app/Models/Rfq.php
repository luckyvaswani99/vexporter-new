<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $buyer_id
 * @property string $status
 * @property string $target_type
 * @property int|null $product_id
 * @property int|null $category_id
 * @property int|null $vertical_id
 * @property string $title
 * @property string|null $description
 * @property int|null $qty
 * @property string|null $unit
 * @property int|null $target_price
 * @property string $currency
 * @property string|null $destination_country
 * @property string|null $incoterm
 * @property Carbon|null $delivery_by
 * @property array<array-key, mixed>|null $attachments
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $buyer
 * @property-read Product|null $product
 * @property-read Collection<int, Quote> $quotes
 * @property-read int|null $quotes_count
 * @property-read Collection<int, Vendor> $vendors
 * @property-read int|null $vendors_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereDeliveryBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereDestinationCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereIncoterm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereTargetPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereVerticalId($value)
 *
 * @mixin \Eloquent
 */
class Rfq extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_QUOTED = 'quoted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CONVERTED = 'converted';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'delivery_by' => 'date',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'rfq_vendors')
            ->withPivot(['invited_at', 'viewed_at', 'status'])
            ->withTimestamps();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
