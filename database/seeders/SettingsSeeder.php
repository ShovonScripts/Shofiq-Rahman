<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name'        => 'কাইজার চৌধুরী সাহিত্য',
            'tagline'          => 'লেখক ও পাঠকদের জন্য',
            'rss_title'        => 'কাইজার চৌধুরী সাহিত্য — RSS',
            'comment_policy'   => 'স্প্যাম নয়, শালীন ভাষা বজায় রাখুন।',
            'social_links'     => json_encode(['facebook' => null, 'twitter' => null, 'youtube' => null]),
            'mail_from_name'   => config('mail.from.name'),
            'mail_from_address'=> config('mail.from.address'),
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
