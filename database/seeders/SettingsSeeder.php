<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'Writer',
            'tagline' => 'লেখক ও পাঠকদের জন্য',
            'rss_title' => 'Writer — RSS',
            'comment_policy' => 'স্প্যাম নয়, শালীন ভাষা বজায় রাখুন।',
            'social_links' => json_encode(['facebook' => null, 'twitter' => null, 'youtube' => null]),
            'mail_from_name' => config('mail.from.name'),
            'mail_from_address' => config('mail.from.address'),

            // SEO defaults
            'meta_title' => 'Writer — বাংলা লেখক ও সাহিত্য',
            'meta_description' => 'বাংলা লেখকদের জন্য একটি সহজ ব্লগিং প্ল্যাটফর্ম। লেখালেখি, বই ও সাহিত্য সম্পর্কিত বিভিন্ন বিষয়ে জানুন এবং নিজের লেখা প্রকাশ করুন।',
            'meta_keywords' => 'বাংলা লেখক, ব্লগিং, বাংলা সাহিত্য, কবিতা, গল্প, উপন্যাস, লেখালেখি',
            'google_analytics_id' => '',
            'google_tag_manager_id' => '',
            'google_site_verification' => '',
            'bing_site_verification' => '',
            'yandex_site_verification' => '',
            'facebook_pixel_id' => '',
            'facebook_app_id' => '',
            'twitter_handle' => '',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
