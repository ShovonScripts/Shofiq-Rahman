<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Seed base content
        |--------------------------------------------------------------------------
        */

        // যদি না থাকে, 1টা ডিফল্ট ইউজার
        $user = User::first() ?? User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'jishovon.bn@gmail.com',
            'password' => bcrypt('password'),
        ]);

        // 5টা ক্যাটেগরি
        $categories = Category::factory()->count(5)->create();

        // 12টা পোস্ট + র‍্যান্ডম ক্যাটেগরি attach
        Post::factory()->count(12)->create([
            'user_id' => $user->id,
        ])->each(function ($post) use ($categories) {
            $post->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        /*
        |--------------------------------------------------------------------------
        | About system seed (Author profile + sample achievements/events)
        |--------------------------------------------------------------------------
        | Make sure you have created: database/seeders/AuthorProfileSeeder.php
        | Then run: php artisan db:seed --class=AuthorProfileSeeder
        | or simply: php artisan db:seed
        */
        $this->call([
            AuthorProfileSeeder::class,
        ]);

        // If you also want your existing settings/admin seeds to run automatically,
        // uncomment lines below (only if these seeders exist in your project):
        // $this->call([
        //     SettingsSeeder::class,
        //     AdminUserSeeder::class,
        // ]);
    }
}
