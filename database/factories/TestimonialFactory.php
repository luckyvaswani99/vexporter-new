<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'initials' => collect(explode(' ', $name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''),
            'designation' => fake()->jobTitle().', '.fake()->company(),
            'country_code' => fake()->randomElement(['GB', 'KE', 'AE', 'US', 'BR']),
            'avatar_gradient' => 'from-blue-400 to-blue-600',
            'rating' => fake()->randomElement([4.5, 5]),
            'body' => fake()->paragraph(),
            'is_featured' => true,
        ];
    }
}
