<?php

namespace App\Models;

use App\Support\Countries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $legal_name
 * @property string|null $logo
 * @property string|null $banner
 * @property string|null $about
 * @property string|null $city
 * @property string|null $state
 * @property string $country_code
 * @property string|null $gst_number
 * @property string|null $pan
 * @property string|null $iec_code
 * @property string|null $cin
 * @property string $status
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property numeric|null $commission_percent
 * @property int|null $min_order_value
 * @property int|null $response_time_hours
 * @property numeric $rating_cache
 * @property int $reviews_count
 * @property int $products_count_cache
 * @property string $avatar_gradient
 * @property string $tag_tone
 * @property string|null $payout_method
 * @property array<array-key, mixed>|null $payout_details
 * @property array<array-key, mixed>|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, VendorBankAccount> $bankAccounts
 * @property-read int|null $bank_accounts_count
 * @property-read mixed $certifications
 * @property-read Collection<int, VendorDocument> $documents
 * @property-read int|null $documents_count
 * @property-read mixed $initial
 * @property-read Collection<int, VendorKycLog> $kycLogs
 * @property-read int|null $kyc_logs_count
 * @property-read mixed $location
 * @property-read User $owner
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read mixed $rating
 * @property-read Collection<int, User> $staff
 * @property-read int|null $staff_count
 * @property-read Collection<int, SubOrder> $subOrders
 * @property-read int|null $sub_orders_count
 * @property-read mixed $tags
 *
 * @method static Builder<static>|Vendor approved()
 * @method static \Database\Factories\VendorFactory factory($count = null, $state = [])
 * @method static Builder<static>|Vendor newModelQuery()
 * @method static Builder<static>|Vendor newQuery()
 * @method static Builder<static>|Vendor onlyTrashed()
 * @method static Builder<static>|Vendor query()
 * @method static Builder<static>|Vendor whereAbout($value)
 * @method static Builder<static>|Vendor whereApprovedAt($value)
 * @method static Builder<static>|Vendor whereApprovedBy($value)
 * @method static Builder<static>|Vendor whereAvatarGradient($value)
 * @method static Builder<static>|Vendor whereBanner($value)
 * @method static Builder<static>|Vendor whereCin($value)
 * @method static Builder<static>|Vendor whereCity($value)
 * @method static Builder<static>|Vendor whereCommissionPercent($value)
 * @method static Builder<static>|Vendor whereCountryCode($value)
 * @method static Builder<static>|Vendor whereCreatedAt($value)
 * @method static Builder<static>|Vendor whereDeletedAt($value)
 * @method static Builder<static>|Vendor whereGstNumber($value)
 * @method static Builder<static>|Vendor whereId($value)
 * @method static Builder<static>|Vendor whereIecCode($value)
 * @method static Builder<static>|Vendor whereLegalName($value)
 * @method static Builder<static>|Vendor whereLogo($value)
 * @method static Builder<static>|Vendor whereMeta($value)
 * @method static Builder<static>|Vendor whereMinOrderValue($value)
 * @method static Builder<static>|Vendor whereName($value)
 * @method static Builder<static>|Vendor wherePan($value)
 * @method static Builder<static>|Vendor wherePayoutDetails($value)
 * @method static Builder<static>|Vendor wherePayoutMethod($value)
 * @method static Builder<static>|Vendor whereProductsCountCache($value)
 * @method static Builder<static>|Vendor whereRatingCache($value)
 * @method static Builder<static>|Vendor whereRejectionReason($value)
 * @method static Builder<static>|Vendor whereResponseTimeHours($value)
 * @method static Builder<static>|Vendor whereReviewsCount($value)
 * @method static Builder<static>|Vendor whereSlug($value)
 * @method static Builder<static>|Vendor whereState($value)
 * @method static Builder<static>|Vendor whereStatus($value)
 * @method static Builder<static>|Vendor whereTagTone($value)
 * @method static Builder<static>|Vendor whereUpdatedAt($value)
 * @method static Builder<static>|Vendor whereUserId($value)
 * @method static Builder<static>|Vendor withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Vendor withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Vendor extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_REJECTED = 'rejected';

    protected $guarded = [];

    protected $hidden = ['payout_details'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'commission_percent' => 'decimal:2',
            'rating_cache' => 'decimal:2',
            'payout_details' => 'encrypted:array',
            'meta' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** KYC decisions and commercial terms are auditable. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'approved_at', 'approved_by', 'rejection_reason', 'commission_percent'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('vendor');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vendor_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(VendorBankAccount::class);
    }

    public function kycLogs(): HasMany
    {
        return $this->hasMany(VendorKycLog::class);
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /** Effective commission — vendor override, else the platform default. */
    public function commissionPercent(): float
    {
        return (float) ($this->commission_percent ?? config('vexporter.commission_percent'));
    }

    protected function initial(): Attribute
    {
        return Attribute::get(fn () => mb_strtoupper(mb_substr($this->name, 0, 1)));
    }

    protected function location(): Attribute
    {
        return Attribute::get(fn () => collect([$this->city, Countries::name($this->country_code)])
            ->filter()
            ->implode(', '));
    }

    protected function rating(): Attribute
    {
        return Attribute::get(fn () => (float) $this->rating_cache);
    }

    protected function productsCount(): Attribute
    {
        return Attribute::get(fn () => (int) $this->products_count_cache);
    }

    /** Category tags shown on the vendor card. */
    protected function tags(): Attribute
    {
        return Attribute::get(fn () => $this->products()
            ->with('category:id,name')
            ->get()
            ->pluck('category.name')
            ->filter()
            ->unique()
            ->take(2)
            ->values()
            ->all());
    }

    protected function certifications(): Attribute
    {
        return Attribute::get(fn () => $this->documents
            ->where('status', VendorDocument::STATUS_VERIFIED)
            ->where('is_public', true)
            ->pluck('label')
            ->filter()
            ->unique()
            ->values()
            ->all());
    }
}
