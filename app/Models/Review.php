<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $vendor_id
 * @property int $user_id
 * @property int|null $order_item_id
 * @property int $rating
 * @property string|null $title
 * @property string|null $body
 * @property array<array-key, mixed>|null $images
 * @property bool $is_verified_purchase
 * @property string $status
 * @property string|null $reply
 * @property Carbon|null $replied_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read User $user
 * @property-read Vendor|null $vendor
 *
 * @method static Builder<static>|Review approved()
 * @method static Builder<static>|Review newModelQuery()
 * @method static Builder<static>|Review newQuery()
 * @method static Builder<static>|Review query()
 * @method static Builder<static>|Review whereBody($value)
 * @method static Builder<static>|Review whereCreatedAt($value)
 * @method static Builder<static>|Review whereId($value)
 * @method static Builder<static>|Review whereImages($value)
 * @method static Builder<static>|Review whereIsVerifiedPurchase($value)
 * @method static Builder<static>|Review whereOrderItemId($value)
 * @method static Builder<static>|Review whereProductId($value)
 * @method static Builder<static>|Review whereRating($value)
 * @method static Builder<static>|Review whereRepliedAt($value)
 * @method static Builder<static>|Review whereReply($value)
 * @method static Builder<static>|Review whereStatus($value)
 * @method static Builder<static>|Review whereTitle($value)
 * @method static Builder<static>|Review whereUpdatedAt($value)
 * @method static Builder<static>|Review whereUserId($value)
 * @method static Builder<static>|Review whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class Review extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_verified_purchase' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
