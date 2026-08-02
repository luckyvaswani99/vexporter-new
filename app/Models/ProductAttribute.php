<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $vertical_id
 * @property int|null $category_id
 * @property string $code
 * @property string $label
 * @property string $type
 * @property string|null $unit
 * @property array<array-key, mixed>|null $options
 * @property bool $is_filterable
 * @property bool $is_required
 * @property bool $is_comparable
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ProductAttributeValue> $values
 * @property-read int|null $values_count
 * @property-read Vertical|null $vertical
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereIsComparable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereIsFilterable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereVerticalId($value)
 *
 * @mixin \Eloquent
 */
class ProductAttribute extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_filterable' => 'boolean',
            'is_required' => 'boolean',
            'is_comparable' => 'boolean',
        ];
    }

    public function vertical(): BelongsTo
    {
        return $this->belongsTo(Vertical::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
