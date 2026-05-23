<?php

namespace App\Http\Controllers;

use App\Models\AuthorProfile;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function show()
    {
        $profile = Cache::remember(CacheKeys::aboutProfile(), now()->addMinutes(30), function () {
            $p = AuthorProfile::query()
                ->where('is_active', true)
                ->with(['achievements', 'events' => function ($q) {
                    $q->where('is_published', true);
                }])
                ->first();

            if (! $p) {
                return null;
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'pen_name' => $p->pen_name,
                'date_of_birth' => optional($p->date_of_birth)->toDateString(),
                'location' => $p->location,
                'email_public' => $p->email_public,
                'website' => $p->website,
                'bio_md' => $p->bio_md,
                'achievements_summary_md' => $p->achievements_summary_md,
                'avatar_url' => $p->avatar_url,
                'cover_url' => $p->cover_url,
                'social_links' => $p->social_links,
                'achievements' => $p->achievements,
                'events' => $p->events,
            ];
        });

        return Inertia::render('About', [
            'profile' => $profile,
        ]);
    }
}
