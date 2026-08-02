<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $product_attribute_id
 * @property string|null $value_text
 * @property numeric|null $value_number
 * @property array<array-key, mixed>|null $value_json
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductAttribute $attribute
 * @property-read mixed $display
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereProductAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereValueJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereValueNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValue whereValueText($value)
 *
 * @mixin \Eloquent
 */
class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['value_json' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    /** Human readable value including the attribute's unit (e.g. "540 Wp"). */
    protected function display(): Attribute
    {
        return Attribute::get(function () {
            $value = $this->value_text
                ?? ($this->value_number !== null ? rtrim(rtrim((string) $this->value_number, '0'), '.') : null)
                ?? (is_array($this->value_json) ? implode(', ', $this->value_json) : null);

            return collect([$value, $this->attribute?->unit])->filter()->implode(' ');
        });
    }
}
