<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // যদি admin ইতিমধ্যে থাকে, ডুপ্লিকেট হবে না
        $admin = User::where('email', 'admin@example.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'), // 🔑 লগইন পাসওয়ার্ড
                'role' => 'admin', // তোমার User টেবিলে যদি 'role' কলাম থাকে
            ]);

            $this->command->info('✅ Admin user created: admin@example.com / password123');
        } else {
            $this->command->warn('⚠️ Admin already exists!');
        }
    }
}
