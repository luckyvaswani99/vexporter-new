<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));
        $price = fake()->numberBetween(5_00, 250_000_00);

        return [
            'vendor_id' => Vendor::factory(),
            'category_id' => Category::factory(),
            'vertical_id' => fn (array $attributes) => Category::find($attributes['category_id'])?->vertical_id
                ?? Category::factory()->create()->vertical_id,
            'type' => Product::TYPE_SIMPLE,
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->bothify('VX-????-####')),
            'hsn_code' => fake()->numerify('########'),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'unit' => fake()->randomElement(['kg', 'unit', 'set', 'pack']),
            'moq' => fake()->randomElement([1, 5, 10, 25, 50, 100]),
            'base_price' => $price,
            'compare_at_price' => fake()->boolean(35) ? (int) ($price * 1.2) : null,
            'currency' => 'USD',
            'stock_qty' => fake()->numberBetween(0, 5000),
            'lead_time_days' => fake()->numberBetween(3, 45),
            'weight_kg' => fake()->randomFloat(2, 0.1, 500),
            'is_active' => true,
            'approval_status' => Product::APPROVAL_APPROVED,
            'rating_cache' => fake()->randomFloat(1, 3.5, 5.0),
            'reviews_count' => fake()->numberBetween(0, 250),
            'published_at' => now()->subDays(fake()->numberBetween(1, 300)),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function quoteOnly(): static
    {
        return $this->state(fn () => ['type' => Product::TYPE_QUOTE_ONLY]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => ['approval_status' => Product::APPROVAL_PENDING]);
    }
}
