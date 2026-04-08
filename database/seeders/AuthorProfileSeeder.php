<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthorProfile;
use App\Models\AuthorAchievement;
use App\Models\AuthorEvent;

class AuthorProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Create (or get) the single site-wide profile
        $profile = AuthorProfile::firstOrCreate([], [
            'name'        => 'Rezoan Nasir',
            'pen_name'    => 'Nas Digital',
            'location'    => 'London, UK',
            'email_public'=> null,
            'website'     => null,
            'bio_md'      => "I'm a poet and creative technologist blending stories with strategy.\n\nThis is **sample markdown** text for your bio section.",
            'achievements_summary_md' => "## Highlights\n- Published 3 books\n- Featured in major magazines\n- Built digital growth platforms",
            // ✅ use *_path columns (not *_url). Leave null if you don't have defaults yet.
            'avatar_path' => null, // e.g. 'defaults/avatar.jpg' if you add a file
            'cover_path'  => null, // e.g. 'defaults/cover.jpg'
            'social_links'=> ['facebook' => '', 'twitter' => '', 'youtube' => '', 'instagram' => ''],
            'is_active'   => true,
        ]);

        // Sample achievements
        AuthorAchievement::insert([
            [
                'author_profile_id' => $profile->id,
                'title' => 'Book Launch: Digital Dreams',
                'year'  => '2021',
                'description_md' => 'Released my first poetry book exploring life and code.',
                'sort_order' => 10,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'author_profile_id' => $profile->id,
                'title' => 'Creative Innovator Award',
                'year'  => '2023',
                'description_md' => 'Recognised for contributions in literature and digital storytelling.',
                'sort_order' => 20,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // Sample life events
        AuthorEvent::insert([
            [
                'author_profile_id' => $profile->id,
                'headline' => 'First Poetry Recital',
                'happened_at' => '2019-05-14',
                'location' => 'Dhaka, Bangladesh',
                'description_md' => 'Performed poetry live for the first time in front of 200+ audience.',
                'image_path' => null,
                'sort_order' => 20,
                'is_published' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'author_profile_id' => $profile->id,
                'headline' => 'Moved to London',
                'happened_at' => '2020-11-01',
                'location' => 'London, UK',
                'description_md' => 'Relocated to pursue new creative opportunities.',
                'image_path' => null,
                'sort_order' => 10,
                'is_published' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }
}
