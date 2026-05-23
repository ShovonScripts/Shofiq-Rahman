<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true); // e.g. "সাহিত্য সমালোচনা"

        // বাংলা/ইংরেজি মিক্স নাম হলে slug এ transliteration সহজ রাখতে ইংরেজি slug
        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
        ];
    }
}
