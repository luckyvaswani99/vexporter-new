<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $order_id
 * @property int|null $sub_order_id
 * @property string $type
 * @property string|null $number
 * @property string|null $file_path
 * @property int|null $issued_by
 * @property Carbon|null $issued_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $issuer
 * @property-read Order|null $order
 * @property-read SubOrder|null $subOrder
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereSubOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDocument whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ExportDocument extends Model
{
    use HasFactory;

    public const TYPE_COMMERCIAL_INVOICE = 'commercial_invoice';

    public const TYPE_PACKING_LIST = 'packing_list';

    public const TYPE_CERTIFICATE_OF_ORIGIN = 'certificate_of_origin';

    public const TYPE_BILL_OF_LADING = 'bill_of_lading';

    public const TYPE_COA = 'certificate_of_analysis';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
