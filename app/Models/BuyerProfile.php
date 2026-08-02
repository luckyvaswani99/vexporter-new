<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $company_name
 * @property string|null $business_type
 * @property string|null $country_code
 * @property string|null $gst_number
 * @property string|null $iec_code
 * @property string|null $import_license_no
 * @property string|null $drug_license_no
 * @property Carbon|null $drug_license_expires_at
 * @property string|null $annual_volume
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereAnnualVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereDrugLicenseExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereDrugLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereGstNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereIecCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereImportLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerProfile whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
class BuyerProfile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'drug_license_expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Pharma gating: a valid, unexpired drug licence on a verified profile. */
    public function canBuyRestrictedPharma(): bool
    {
        return $this->verified_at !== null
            && $this->drug_license_no !== null
            && ($this->drug_license_expires_at === null || $this->drug_license_expires_at->isFuture());
    }
}
