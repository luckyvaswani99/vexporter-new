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
 * @property string|null $label
 * @property string $file_path
 * @property bool $requires_login
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereRequiresLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDocument whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductDocument extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['requires_login' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
