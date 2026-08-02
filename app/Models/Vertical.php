<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $icon
 * @property string|null $watermark_icon
 * @property string|null $gradient_class
 * @property string|null $chip_class
 * @property string $accent
 * @property string|null $tagline
 * @property int $products_count_cache
 * @property int $sort_order
 * @property int $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Category> $categories
 * @property-read int|null $categories_count
 * @property-read mixed $chips
 * @property-read mixed $gradient
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read mixed $products_label
 *
 * @method static \Database\Factories\VerticalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereAccent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereChipClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereGradientClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereProductsCountCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vertical whereWatermarkIcon($value)
 *
 * @mixin \Eloquent
 */
class Vertical extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function gradient(): Attribute
    {
        return Attribute::get(fn () => $this->gradient_class);
    }

    protected function productsLabel(): Attribute
    {
        return Attribute::get(fn () => number_format($this->products_count_cache).'+ Products');
    }

    /** Chips shown on the homepage category cards. */
    protected function chips(): Attribute
    {
        return Attribute::get(function () {
            $categories = $this->relationLoaded('categories') ? $this->categories : $this->categories()->get();
            $shown = $categories->take(3);
            $remaining = max(0, $categories->count() - $shown->count());

            return $shown
                ->map(fn (Category $category) => str($category->name)->before(' &')->limit(18, '')->toString())
                ->when($remaining > 0, fn ($chips) => $chips->push("+{$remaining} more"))
                ->values()
                ->all();
        });
    }
}
