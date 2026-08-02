<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $type
 * @property string|null $number
 * @property string|null $file_path
 * @property Carbon|null $expires_at
 * @property bool $is_primary
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCertificate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductCertificate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
