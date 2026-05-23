<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'নাটক', 'slug' => 'natok'],
            ['name' => 'উপন্যাস', 'slug' => 'upanyas'],
            ['name' => 'কাব্য', 'slug' => 'kobita'],
            ['name' => 'ছোটগল্প', 'slug' => 'short-story'],
            ['name' => 'রচনা', 'slug' => 'essay'],
            ['name' => 'জীবনী', 'slug' => 'biography'],
            ['name' => 'বিশ্বকাথা', 'slug' => 'fantasy'],
            ['name' => 'সামাজিক উপদ্ব)', 'slug' => 'social-issue'],
            ['name' => 'মুক্তিযুদ্ধ', 'slug' => 'liberation-war'],
            ['name' => 'বই সমীลักษণ', 'slug' => 'book-review'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        $this->command->info('✅ Categories seeded: '.Category::count());
    }
}
