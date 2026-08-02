<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $dispute_id
 * @property int $user_id
 * @property string $message
 * @property array<array-key, mixed>|null $attachments
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Dispute $dispute
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereDisputeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereUserId($value)
 *
 * @mixin \Eloquent
 */
class DisputeMessage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
