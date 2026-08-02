<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property string $type
 * @property string|null $label
 * @property string|null $number
 * @property string|null $issuing_authority
 * @property Carbon|null $issued_at
 * @property Carbon|null $expires_at
 * @property string|null $file_path
 * @property string $status
 * @property int|null $reviewed_by
 * @property string|null $review_note
 * @property int $is_public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereIssuingAuthority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereReviewNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorDocument whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class VendorDocument extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
