<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Vertical;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'vertical_id' => Vertical::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => 'fa-box',
            'icon_color' => 'text-gray-200',
            'image_gradient' => 'from-gray-50 to-gray-100',
        ];
    }
}
