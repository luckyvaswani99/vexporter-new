<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quote_id
 * @property int $sender_id
 * @property string $body
 * @property array<array-key, mixed>|null $attachments
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Quote $quote
 * @property-read User $sender
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereQuoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class QuoteMessage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['attachments' => 'array'];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
