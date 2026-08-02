<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vendor> */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory()->state(['type' => User::TYPE_VENDOR]),
            'name' => $name,
            'slug' => Str::slug($name),
            'legal_name' => $name.' Pvt Ltd',
            'about' => fake()->paragraph(),
            'city' => fake()->randomElement(['Mumbai', 'Hyderabad', 'Surat', 'Bangalore', 'Ahmedabad', 'Chennai']),
            'state' => fake()->randomElement(['Maharashtra', 'Telangana', 'Gujarat', 'Karnataka', 'Tamil Nadu']),
            'country_code' => 'IN',
            'gst_number' => strtoupper(fake()->bothify('##???####?#Z#')),
            'pan' => strtoupper(fake()->bothify('?????####?')),
            'iec_code' => fake()->numerify('##########'),
            'status' => Vendor::STATUS_APPROVED,
            'approved_at' => now()->subMonths(fake()->numberBetween(1, 24)),
            'response_time_hours' => fake()->numberBetween(2, 24),
            'rating_cache' => fake()->randomFloat(1, 4.0, 5.0),
            'reviews_count' => fake()->numberBetween(10, 400),
            'avatar_gradient' => fake()->randomElement([
                'from-blue-500 to-blue-600',
                'from-yellow-500 to-orange-500',
                'from-purple-500 to-pink-500',
                'from-green-500 to-emerald-600',
                'from-cyan-500 to-blue-500',
            ]),
            'tag_tone' => fake()->randomElement(['blue', 'orange', 'purple', 'green', 'cyan', 'teal', 'indigo']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Vendor::STATUS_PENDING,
            'approved_at' => null,
        ]);
    }
}
