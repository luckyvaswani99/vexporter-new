<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int|null $actor_id
 * @property string $action
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $actor
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorKycLog whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class VendorKycLog extends Model
{
    protected $guarded = [];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
