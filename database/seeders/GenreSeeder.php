<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            ['name' => 'সমকালীন', 'slug' => 'contemporary'],
            ['name' => 'রোমান্টিক', 'slug' => 'romantic'],
            ['name' => 'ইতিহাস', 'slug' => 'historical'],
            ['name' => 'সামাজিক', 'slug' => 'social'],
            ['name' => 'রহস্য', 'slug' => 'mystery'],
            ['name' => 'কৌতুক', 'slug' => 'humor'],
            ['name' => 'বিজ্ঞান-কল্পনা', 'slug' => 'science-fiction'],
            ['name' => 'উদ্দীপনা', 'slug' => 'inspirational'],
            ['name' => 'ফikশন মিক্সড', 'slug' => 'mixed'],
            ['name' => 'ילדֶℓমGesture গ vij', 'slug' => 'travel'],
        ];

        foreach ($genres as $genre) {
            Genre::firstOrCreate(['slug' => $genre['slug']], $genre);
        }

        $this->command->info('✅ Genres seeded: '.Genre::count());
    }
}
