<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use App\Models\Setting;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HomepageController extends Controller
{
    /**
     * Show homepage manager.
     */
    public function edit()
    {
        return Inertia::render('Dashboard/Homepage/Edit', [
            'banners' => HomeBanner::orderBy('sort_order')->get()
                ->map(fn (HomeBanner $b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'link' => $b->link,
                    'is_active' => (bool) $b->is_active,
                    'sort_order' => (int) $b->sort_order,
                    'image_url' => $b->image_url, // accessor on model
                ]),
            'author' => [
                'name' => Setting::get('home_author_name', ''),
                'bio' => Setting::get('home_author_bio_md', ''),
                'photo_url' => ($p = Setting::get('home_author_photo'))
                    ? storage_url($p)
                    : null,
            ],
        ]);
    }

    /**
     * Create a new banner.
     */
    public function storeBanner(Request $request)
    {
        // Trim common text fields up front
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'link' => trim((string) $request->input('link')),
        ]);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'link' => ['nullable', 'url', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:3072'], // ~3MB
        ]);

        $path = $request->file('image')->store('home/banners', 'public');

        $maxSort = (int) HomeBanner::max('sort_order');

        HomeBanner::create([
            'title' => $data['title'] ?: null,
            'link' => $data['link'] ?: null,
            'image' => $path,
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        Cache::forget(CacheKeys::homeBanners());

        return redirect()->back()->with('success', 'Banner added successfully.');
    }

    /**
     * Update a banner (text/active/swap image).
     */
    public function updateBanner(Request $request, HomeBanner $banner)
    {
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'link' => trim((string) $request->input('link')),
            'is_active' => $request->boolean('is_active'),
        ]);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'link' => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:3072'],
        ]);

        // Handle new image
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('home/banners', 'public');
        } else {
            // Remove image key if not updating image
            unset($data['image']);
        }

        $banner->update($data);

        Cache::forget(CacheKeys::homeBanners());

        return redirect()->back()->with('success', 'Banner updated successfully.');
    }

    /**
     * Delete a banner.
     */
    public function destroyBanner(HomeBanner $banner)
    {
        // Delete associated image
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        Cache::forget(CacheKeys::homeBanners());

        return redirect()->back()->with('success', 'Banner deleted successfully.');
    }

    /**
     * Persist new sort order.
     *
     * Expects payload like:
     * items: [{ id: 5, sort_order: 0 }, { id: 7, sort_order: 1 }, ...]
     */
    public function sortBanners(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:home_banners,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $item) {
            HomeBanner::where('id', $item['id'])->update([
                'sort_order' => (int) $item['sort_order'],
            ]);
        }

        Cache::forget(CacheKeys::homeBanners());

        return response()->json(['success' => true, 'message' => 'Banner order updated successfully.']);
    }

    /**
     * Update author card (name/bio/photo).
     */
    public function updateAuthor(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'bio' => (string) $request->input('bio', ''),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:3072'],
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $oldPhoto = Setting::get('home_author_photo');

            // Delete old photo if exists
            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }

            $path = $request->file('photo')->store('home/author', 'public');
            Setting::set('home_author_photo', $path);
        }

        // Update name and bio
        Setting::set('home_author_name', $data['name']);
        Setting::set('home_author_bio_md', $data['bio'] ?? '');

        return redirect()->back()->with('success', 'Author information updated successfully.');
    }

    /**
     * Toggle banner active status
     */
    public function toggleBanner(HomeBanner $banner)
    {
        $banner->update([
            'is_active' => ! $banner->is_active,
        ]);

        Cache::forget(CacheKeys::homeBanners());

        return redirect()->back()->with('success', 'Banner status updated successfully.');
    }

    /**
     * Delete author photo
     */
    public function deleteAuthorPhoto()
    {
        $oldPhoto = Setting::get('home_author_photo');

        if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }

        Setting::set('home_author_photo', null);

        return redirect()->back()->with('success', 'Author photo removed successfully.');
    }
}
