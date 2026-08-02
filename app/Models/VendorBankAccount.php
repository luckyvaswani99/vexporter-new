<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property string $account_holder
 * @property string $account_no
 * @property string|null $ifsc
 * @property string|null $swift
 * @property string|null $bank_name
 * @property string|null $branch
 * @property string $currency
 * @property bool $is_primary
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereAccountHolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereIfsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereSwift($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorBankAccount whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
class VendorBankAccount extends Model
{
    protected $guarded = [];

    protected $hidden = ['account_no'];

    protected function casts(): array
    {
        return [
            // Account numbers are encrypted at rest; only payouts read them back.
            'account_no' => 'encrypted',
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
