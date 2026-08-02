<?php

namespace App\Models;

use App\Support\Html;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int $vertical_id
 * @property int $category_id
 * @property int|null $brand_id
 * @property string $type
 * @property string $name
 * @property string $slug
 * @property string|null $sku
 * @property string|null $hsn_code
 * @property string|null $short_description
 * @property string|null $description
 * @property string $unit
 * @property int $moq
 * @property int $order_increment
 * @property int $base_price
 * @property int|null $compare_at_price
 * @property string $currency
 * @property int $stock_qty
 * @property string $stock_status
 * @property int|null $lead_time_days
 * @property numeric|null $weight_kg
 * @property numeric|null $length_cm
 * @property numeric|null $width_cm
 * @property numeric|null $height_cm
 * @property bool $is_active
 * @property bool $is_featured
 * @property bool $is_bestseller
 * @property bool $requires_license
 * @property string $approval_status
 * @property string|null $rejection_reason
 * @property numeric $rating_cache
 * @property-read int|null $reviews_count
 * @property int $views_count
 * @property string|null $badge
 * @property string|null $badge_tone
 * @property string|null $icon
 * @property string|null $icon_color
 * @property string|null $image_gradient
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, ProductAttributeValue> $attributeValues
 * @property-read int|null $attribute_values_count
 * @property-read Brand|null $brand
 * @property-read Category $category
 * @property-read mixed $category_label
 * @property-read Collection<int, ProductCertificate> $certificates
 * @property-read int|null $certificates_count
 * @property-read mixed $certification
 * @property-read mixed $compare_at_label
 * @property-read Collection<int, ProductDocument> $documents
 * @property-read int|null $documents_count
 * @property-read Collection<int, ProductImage> $images
 * @property-read int|null $images_count
 * @property-read mixed $is_quote_only
 * @property-read mixed $price_label
 * @property-read mixed $primary_image
 * @property-read mixed $rating
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, ProductTierPrice> $tierPrices
 * @property-read int|null $tier_prices_count
 * @property-read mixed $unit_label
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read int|null $variants_count
 * @property-read Vendor|null $vendor
 * @property-read mixed $vendor_avatar_class
 * @property-read mixed $vendor_icon_class
 * @property-read mixed $vendor_name
 * @property-read mixed $vendor_slug
 * @property-read Vertical $vertical
 *
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product featured()
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product onlyTrashed()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product visible()
 * @method static Builder<static>|Product whereApprovalStatus($value)
 * @method static Builder<static>|Product whereBadge($value)
 * @method static Builder<static>|Product whereBadgeTone($value)
 * @method static Builder<static>|Product whereBasePrice($value)
 * @method static Builder<static>|Product whereBrandId($value)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCompareAtPrice($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereCurrency($value)
 * @method static Builder<static>|Product whereDeletedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereHeightCm($value)
 * @method static Builder<static>|Product whereHsnCode($value)
 * @method static Builder<static>|Product whereIcon($value)
 * @method static Builder<static>|Product whereIconColor($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereImageGradient($value)
 * @method static Builder<static>|Product whereIsActive($value)
 * @method static Builder<static>|Product whereIsBestseller($value)
 * @method static Builder<static>|Product whereIsFeatured($value)
 * @method static Builder<static>|Product whereLeadTimeDays($value)
 * @method static Builder<static>|Product whereLengthCm($value)
 * @method static Builder<static>|Product whereMoq($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product whereOrderIncrement($value)
 * @method static Builder<static>|Product wherePublishedAt($value)
 * @method static Builder<static>|Product whereRatingCache($value)
 * @method static Builder<static>|Product whereRejectionReason($value)
 * @method static Builder<static>|Product whereRequiresLicense($value)
 * @method static Builder<static>|Product whereReviewsCount($value)
 * @method static Builder<static>|Product whereSeoDescription($value)
 * @method static Builder<static>|Product whereSeoTitle($value)
 * @method static Builder<static>|Product whereShortDescription($value)
 * @method static Builder<static>|Product whereSku($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereStockQty($value)
 * @method static Builder<static>|Product whereStockStatus($value)
 * @method static Builder<static>|Product whereType($value)
 * @method static Builder<static>|Product whereUnit($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product whereVendorId($value)
 * @method static Builder<static>|Product whereVerticalId($value)
 * @method static Builder<static>|Product whereViewsCount($value)
 * @method static Builder<static>|Product whereWeightKg($value)
 * @method static Builder<static>|Product whereWidthCm($value)
 * @method static Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Product withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    public const TYPE_SIMPLE = 'simple';

    public const TYPE_VARIABLE = 'variable';

    public const TYPE_QUOTE_ONLY = 'quote_only';

    public const TYPE_SERVICE_EPC = 'service_epc';

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_SIMPLE => 'Simple',
        self::TYPE_VARIABLE => 'Variable',
        self::TYPE_QUOTE_ONLY => 'Quote only',
        self::TYPE_SERVICE_EPC => 'EPC / service',
    ];

    /** Shown next to each option so the choice is not a guess. */
    public const TYPE_DESCRIPTIONS = [
        self::TYPE_SIMPLE => 'One price, one SKU. Buyers add it straight to the cart.',
        self::TYPE_VARIABLE => 'Sold in several options (size, grade, wattage). Add the options below — buyers pick one before adding to the cart.',
        self::TYPE_QUOTE_ONLY => 'No public price. The buy button becomes "Request a quote" and the item cannot be added to the cart.',
        self::TYPE_SERVICE_EPC => 'Project or installation work, quoted per enquiry. Behaves like Quote only.',
    ];

    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

    /** Units that read as "/ kg" style suffixes on the storefront. */
    public const UNIT_LABELS = [
        'kg' => '/ kg',
        'ton' => '/ ton',
        'unit' => '/ unit',
        'set' => '/ set',
        'pack' => '/ pack',
        'piece' => '/ piece',
        'litre' => '/ litre',
        'kw' => '/ kW',
        'turnkey' => 'turnkey',
    ];

    /**
     * Vendor tone → literal Tailwind classes. Written out in full (never
     * interpolated) so the Tailwind scanner keeps them in the build.
     */
    public const TONE_CLASSES = [
        'blue' => ['avatar' => 'bg-blue-100', 'icon' => 'text-blue-600'],
        'green' => ['avatar' => 'bg-green-100', 'icon' => 'text-green-600'],
        'purple' => ['avatar' => 'bg-purple-100', 'icon' => 'text-purple-600'],
        'pink' => ['avatar' => 'bg-pink-100', 'icon' => 'text-pink-600'],
        'orange' => ['avatar' => 'bg-orange-100', 'icon' => 'text-orange-600'],
        'yellow' => ['avatar' => 'bg-yellow-100', 'icon' => 'text-yellow-600'],
        'cyan' => ['avatar' => 'bg-cyan-100', 'icon' => 'text-cyan-600'],
        'indigo' => ['avatar' => 'bg-indigo-100', 'icon' => 'text-indigo-600'],
        'teal' => ['avatar' => 'bg-teal-100', 'icon' => 'text-teal-600'],
        'red' => ['avatar' => 'bg-red-100', 'icon' => 'text-brand-red'],
        'gray' => ['avatar' => 'bg-gray-100', 'icon' => 'text-gray-500'],
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'requires_license' => 'boolean',
            'rating_cache' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Both descriptions come from a rich-text editor and are rendered
     * unescaped, so they are reduced to an allow-list on the way in — at the
     * model, not the form, so the vendor panel and admin panel are both covered.
     */
    protected function shortDescription(): Attribute
    {
        return Attribute::set(fn (?string $value) => Html::sanitize($value));
    }

    protected function description(): Attribute
    {
        return Attribute::set(fn (?string $value) => Html::sanitize($value));
    }

    /** Moderation decisions and price changes are auditable. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'base_price', 'is_active', 'approval_status', 'rejection_reason', 'requires_license'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('product');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vertical(): BelongsTo
    {
        return $this->belongsTo(Vertical::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class)->orderBy('min_qty');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(ProductCertificate::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Only products a buyer is allowed to see on the storefront. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('approval_status', self::APPROVAL_APPROVED)
            ->whereHas('vendor', fn (Builder $vendor) => $vendor->where('status', Vendor::STATUS_APPROVED));
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function requiresQuote(): bool
    {
        return in_array($this->type, [self::TYPE_QUOTE_ONLY, self::TYPE_SERVICE_EPC], true)
            || $this->requires_license;
    }

    /**
     * Unit price for a given quantity, honouring the tier price ladder.
     *
     * A variant-specific slab wins over a product-wide one; with no matching
     * slab the variant's own price applies, and failing that the base price.
     */
    public function priceForQty(int $qty, ?ProductVariant $variant = null): int
    {
        $tier = $this->tierPrices
            ->filter(fn (ProductTierPrice $tier) => $qty >= $tier->min_qty
                && ($tier->max_qty === null || $qty <= $tier->max_qty)
                && ($tier->product_variant_id === null || $tier->product_variant_id === $variant?->id))
            ->sortByDesc('product_variant_id')
            ->sortByDesc('min_qty')
            ->first();

        return (int) ($tier?->price ?? $variant?->price ?? $this->base_price);
    }

    /** The option a product page should open on. */
    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants->firstWhere('is_default', true) ?? $this->variants->first();
    }

    protected function isQuoteOnly(): Attribute
    {
        return Attribute::get(fn () => $this->requiresQuote());
    }

    protected function primaryImage(): Attribute
    {
        return Attribute::get(fn () => ($this->images->firstWhere('is_primary', true) ?? $this->images->first())?->path);
    }

    protected function priceLabel(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->base_price, $this->currency));
    }

    protected function compareAtLabel(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->compare_at_price, $this->currency));
    }

    protected function unitLabel(): Attribute
    {
        return Attribute::get(fn () => self::UNIT_LABELS[$this->unit] ?? '/ '.$this->unit);
    }

    protected function rating(): Attribute
    {
        return Attribute::get(fn () => (float) $this->rating_cache);
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::get(fn () => $this->category?->name);
    }

    protected function vendorName(): Attribute
    {
        return Attribute::get(fn () => $this->vendor?->name);
    }

    protected function vendorSlug(): Attribute
    {
        return Attribute::get(fn () => $this->vendor?->slug);
    }

    protected function vendorAvatarClass(): Attribute
    {
        return Attribute::get(fn () => self::TONE_CLASSES[$this->vendor?->tag_tone ?? 'gray']['avatar']
            ?? self::TONE_CLASSES['gray']['avatar']);
    }

    protected function vendorIconClass(): Attribute
    {
        return Attribute::get(fn () => self::TONE_CLASSES[$this->vendor?->tag_tone ?? 'gray']['icon']
            ?? self::TONE_CLASSES['gray']['icon']);
    }

    /** Headline certificate shown on the product card. */
    protected function certification(): Attribute
    {
        return Attribute::get(fn () => $this->certificates
            ->sortByDesc('is_primary')
            ->first()?->type);
    }
}
