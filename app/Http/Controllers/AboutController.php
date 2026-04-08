<?php

namespace App\Http\Controllers;

use App\Models\AuthorProfile;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function show()
    {
        // For single-author site: the active profile
        $profile = AuthorProfile::query()
            ->where('is_active', true)
            ->with(['achievements', 'events' => function($q){ $q->where('is_published', true); }])
            ->first();

        return Inertia::render('About', [
            'profile' => $profile ? [
                'id'          => $profile->id,
                'name'        => $profile->name,
                'pen_name'    => $profile->pen_name,
                'date_of_birth'=> optional($profile->date_of_birth)->toDateString(),
                'location'    => $profile->location,
                'email_public'=> $profile->email_public,
                'website'     => $profile->website,
                'bio_md'      => $profile->bio_md,
                'achievements_summary_md' => $profile->achievements_summary_md,
                'avatar_url'  => $profile->avatar_url,
                'cover_url'   => $profile->cover_url,
                'social_links'=> $profile->social_links,
                'achievements'=> $profile->achievements,
                'events'      => $profile->events,
            ] : null
        ]);
    }
}
