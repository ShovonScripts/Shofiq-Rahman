<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Running DatabaseSeeder...');

        // Create default user if none exists
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Demo User',
                'email' => 'demo@writer.bd',
                'password' => bcrypt('demo123'),
                'role' => 'reader',
                'is_active' => true,
            ]);
        }

        // Seed all datasets in order
        $this->call([
            CategorySeeder::class,
            GenreSeeder::class,
            SettingsSeeder::class,
            AdminUserSeeder::class,
            AuthorProfileSeeder::class,
            DemoDataSeeder::class,
        ]);

        $this->command->info('✅ All seeders completed!');
    }
}
