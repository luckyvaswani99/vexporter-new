<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $initials
 * @property string|null $designation
 * @property string|null $company
 * @property string|null $country_code
 * @property string|null $avatar
 * @property string $avatar_gradient
 * @property float $rating
 * @property string $body
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\TestimonialFactory factory($count = null, $state = [])
 * @method static Builder<static>|Testimonial featured()
 * @method static Builder<static>|Testimonial newModelQuery()
 * @method static Builder<static>|Testimonial newQuery()
 * @method static Builder<static>|Testimonial query()
 * @method static Builder<static>|Testimonial whereAvatar($value)
 * @method static Builder<static>|Testimonial whereAvatarGradient($value)
 * @method static Builder<static>|Testimonial whereBody($value)
 * @method static Builder<static>|Testimonial whereCompany($value)
 * @method static Builder<static>|Testimonial whereCountryCode($value)
 * @method static Builder<static>|Testimonial whereCreatedAt($value)
 * @method static Builder<static>|Testimonial whereDesignation($value)
 * @method static Builder<static>|Testimonial whereId($value)
 * @method static Builder<static>|Testimonial whereInitials($value)
 * @method static Builder<static>|Testimonial whereIsFeatured($value)
 * @method static Builder<static>|Testimonial whereName($value)
 * @method static Builder<static>|Testimonial whereRating($value)
 * @method static Builder<static>|Testimonial whereSortOrder($value)
 * @method static Builder<static>|Testimonial whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Testimonial extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->orderBy('sort_order');
    }
}
