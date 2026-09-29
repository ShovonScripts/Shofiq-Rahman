<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAuthorEventRequest;
use App\Http\Requests\Dashboard\UpdateAuthorEventRequest;
use App\Models\AuthorEvent;
use App\Models\AuthorProfile;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AuthorEventController extends Controller
{
    public function index()
    {
        $profile = AuthorProfile::first();
        $items = $profile ? $profile->events()->get() : collect();

        return Inertia::render('Dashboard/About/Events/Index', [
            'profile' => $profile,
            'events' => $items,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/About/Events/Create');
    }

    public function store(StoreAuthorEventRequest $request)
    {
        $profile = AuthorProfile::firstOrCreate([], []);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('author-events', 'public');
        }

        $profile->events()->create($data);
        Cache::forget(CacheKeys::aboutProfile());

        return redirect()->route('dashboard.about.events.index')->with('success', 'Event added.');
    }

    public function edit(AuthorEvent $event)
    {
        return Inertia::render('Dashboard/About/Events/Edit', [
            'event' => array_merge($event->toArray(), [
                'happened_at' => $event->happened_at?->toDateString(),
                'image_url' => $event->image_url,
            ]),
        ]);
    }

    public function update(UpdateAuthorEventRequest $request, AuthorEvent $event)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
            $data['image_path'] = $request->file('image')->store('author-events', 'public');
        }

        $event->update($data);
        Cache::forget(CacheKeys::aboutProfile());

        return redirect()->route('dashboard.about.events.index')->with('success', 'Event updated.');
    }

    public function destroy(AuthorEvent $event)
    {
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }
        $event->delete();
        Cache::forget(CacheKeys::aboutProfile());

        return back()->with('success', 'Event deleted.');
    }
}
