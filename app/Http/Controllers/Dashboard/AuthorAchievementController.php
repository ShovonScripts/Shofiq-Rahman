<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAuthorAchievementRequest;
use App\Http\Requests\Dashboard\UpdateAuthorAchievementRequest;
use App\Models\AuthorAchievement;
use App\Models\AuthorProfile;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AuthorAchievementController extends Controller
{
    public function index()
    {
        $profile = AuthorProfile::first();
        $items = $profile ? $profile->achievements : [];

        return Inertia::render('Dashboard/About/Achievements/Index', [
            'profile' => $profile,
            'achievements' => $items,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/About/Achievements/Create');
    }

    public function store(StoreAuthorAchievementRequest $request)
    {
        $profile = AuthorProfile::firstOrCreate([], []);
        $profile->achievements()->create($request->validated());
        Cache::forget(CacheKeys::aboutProfile());

        return redirect()->route('dashboard.about.achievements.index')->with('success', 'Achievement added.');
    }

    public function edit(AuthorAchievement $achievement)
    {
        return Inertia::render('Dashboard/About/Achievements/Edit', [
            'achievement' => $achievement,
        ]);
    }

    public function update(UpdateAuthorAchievementRequest $request, AuthorAchievement $achievement)
    {
        $achievement->update($request->validated());
        Cache::forget(CacheKeys::aboutProfile());

        return redirect()->route('dashboard.about.achievements.index')->with('success', 'Achievement updated.');
    }

    public function destroy(AuthorAchievement $achievement)
    {
        $achievement->delete();
        Cache::forget(CacheKeys::aboutProfile());

        return back()->with('success', 'Achievement deleted.');
    }
}
