<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $min_qty
 * @property int|null $max_qty
 * @property int $price
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $price_label
 * @property-read Product|null $product
 * @property-read mixed $qty_label
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereMaxQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereMinQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTierPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductTierPrice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function priceLabel(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->price, $this->currency));
    }

    protected function qtyLabel(): Attribute
    {
        return Attribute::get(fn () => $this->max_qty
            ? "{$this->min_qty} – {$this->max_qty}"
            : "{$this->min_qty}+");
    }
}
