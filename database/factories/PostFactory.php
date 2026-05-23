<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        // শিরোনাম বাংলা/ইংরেজি যাই হতে পারে
        $title = $this->faker->sentence(4);
        $body = $this->faker->paragraphs(mt_rand(3, 7), true);
        $readingTime = max(1, (int) ceil(str_word_count(strip_tags($body)) / 180)); // আনুমানিক

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'excerpt' => $this->faker->sentence(20),
            'body' => $body,
            'cover' => null, // পরে ইমেজ দিলে হবে
            'reading_time' => $readingTime,
            'status' => $this->faker->randomElement(['draft', 'published', 'published', 'published']),
            'published_at' => now()->subDays(mt_rand(0, 90)),
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
