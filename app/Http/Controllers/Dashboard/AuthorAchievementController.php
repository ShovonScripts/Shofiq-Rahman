<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAuthorAchievementRequest;
use App\Http\Requests\Dashboard\UpdateAuthorAchievementRequest;
use App\Models\AuthorAchievement;
use App\Models\AuthorProfile;
use Inertia\Inertia;

class AuthorAchievementController extends Controller
{
    public function index() {
        $profile = AuthorProfile::first();
        $items = $profile ? $profile->achievements : [];
        return Inertia::render('Dashboard/About/Achievements/Index', [
            'profile' => $profile,
            'items' => $items,
        ]);
    }

    public function create() {
        return Inertia::render('Dashboard/About/Achievements/Create');
    }

    public function store(StoreAuthorAchievementRequest $request) {
        $profile = AuthorProfile::firstOrCreate([], []);
        $profile->achievements()->create($request->validated());
        return redirect()->route('dashboard.about.achievements.index')->with('success','Achievement added.');
    }

    public function edit(AuthorAchievement $achievement) {
        return Inertia::render('Dashboard/About/Achievements/Edit', [
            'item' => $achievement
        ]);
    }

    public function update(UpdateAuthorAchievementRequest $request, AuthorAchievement $achievement) {
        $achievement->update($request->validated());
        return redirect()->route('dashboard.about.achievements.index')->with('success','Achievement updated.');
    }

    public function destroy(AuthorAchievement $achievement) {
        $achievement->delete();
        return back()->with('success','Achievement deleted.');
    }
}
