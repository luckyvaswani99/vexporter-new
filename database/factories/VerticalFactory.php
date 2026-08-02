<?php

namespace Database\Factories;

use App\Models\Vertical;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vertical> */
class VerticalFactory extends Factory
{
    protected $model = Vertical::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => 'fa-store',
            'watermark_icon' => 'fa-store',
            'gradient_class' => 'gradient-main',
            'chip_class' => 'bg-gray-100 text-gray-600',
            'accent' => 'gray',
            'tagline' => fake()->sentence(),
        ];
    }
}
