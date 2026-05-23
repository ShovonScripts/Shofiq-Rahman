<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateAuthorProfileRequest;
use App\Models\AuthorProfile;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AuthorProfileController extends Controller
{
    public function edit()
    {
        $profile = AuthorProfile::first(); // single-row pattern; create one on seed if absent

        return Inertia::render('Dashboard/About/Edit', [
            'profile' => $profile,
        ]);
    }

    public function update(UpdateAuthorProfileRequest $request)
    {
        $data = $request->validated();

        $profile = AuthorProfile::firstOrCreate([], []);
        // Handle file uploads
        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('authors', 'public');
        }
        if ($request->hasFile('cover')) {
            if ($profile->cover_path) {
                Storage::disk('public')->delete($profile->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('authors', 'public');
        }

        $profile->fill($data)->save();

        Cache::forget(CacheKeys::aboutProfile());

        return back()->with('success', 'Author profile updated.');
    }
}
