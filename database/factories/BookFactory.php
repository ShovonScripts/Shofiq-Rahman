<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'subtitle' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(2, true),
            'cover' => null,
            'isbn' => $this->faker->isbn13,
            'language' => $this->faker->randomElement(['bn', 'en']),
            'pages' => $this->faker->numberBetween(50, 500),
            'release_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'status' => $this->faker->randomElement(['draft', 'published', 'published', 'published']),
            'published_at' => now()->subDays($this->faker->numberBetween(0, 90)),
            'meta_json' => ['keywords' => $this->faker->words(3)],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now()->subDays($this->faker->numberBetween(0, 90)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
