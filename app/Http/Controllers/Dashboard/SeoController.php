<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeoController extends Controller
{
    public function index()
    {
        $s = Setting::allCached();

        $fields = [
            'meta_title' => $s['meta_title'] ?? '',
            'meta_description' => $s['meta_description'] ?? '',
            'meta_keywords' => $s['meta_keywords'] ?? '',
            'meta_image' => $s['meta_image'] ?? '',
            'google_analytics_id' => $s['google_analytics_id'] ?? '',
            'google_tag_manager_id' => $s['google_tag_manager_id'] ?? '',
            'google_site_verification' => $s['google_site_verification'] ?? '',
            'bing_site_verification' => $s['bing_site_verification'] ?? '',
            'yandex_site_verification' => $s['yandex_site_verification'] ?? '',
            'facebook_pixel_id' => $s['facebook_pixel_id'] ?? '',
            'facebook_app_id' => $s['facebook_app_id'] ?? '',
            'twitter_handle' => $s['twitter_handle'] ?? '',
            'canonical_url' => $s['canonical_url'] ?? '',
        ];

        return Inertia::render('Dashboard/Seo/Index', [
            'seo' => $fields,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_image' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:50|regex:/^G-[A-Z0-9]+$/',
            'google_tag_manager_id' => 'nullable|string|max:50|regex:/^GTM-[A-Z0-9]+$/',
            'google_site_verification' => 'nullable|string|max:255',
            'bing_site_verification' => 'nullable|string|max:255',
            'yandex_site_verification' => 'nullable|string|max:255',
            'facebook_pixel_id' => ['nullable', 'string', 'max:50', 'regex:/\A[0-9]+\z/'],
            'facebook_app_id' => ['nullable', 'string', 'max:50', 'regex:/\A[0-9]+\z/'],
            'twitter_handle' => 'nullable|string|max:50|regex:/^@?[A-Za-z0-9_]+$/',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->back()->with('success', 'SEO settings updated successfully.');
    }

    public function analyze()
    {
        $missingMetaPosts = Post::published()
            ->get()
            ->filter(function ($post) {
                $meta = $post->meta_json ?? [];

                return empty($meta['title']) || empty($meta['description']);
            })
            ->values();

        $missingMetaBooks = Book::published()
            ->get()
            ->filter(function ($book) {
                $meta = $book->meta_json ?? [];

                return empty($meta['title']) || empty($meta['description']);
            })
            ->values();

        $lengthFunction = DB::getDriverName() === 'sqlite' ? 'LENGTH' : 'CHAR_LENGTH';
        $postsWithShortContent = Post::published()
            ->whereRaw("{$lengthFunction}(COALESCE(body, '')) < ?", [300])
            ->get(['id', 'title', 'slug'])
            ->values();

        return Inertia::render('Dashboard/Seo/Analyze', [
            'missingMetaPosts' => $missingMetaPosts,
            'missingMetaBooks' => $missingMetaBooks,
            'shortPosts' => $postsWithShortContent,
            'stats' => [
                'totalPosts' => Post::published()->count(),
                'totalBooks' => Book::published()->count(),
                'goodMetaPosts' => Post::published()->count() - $missingMetaPosts->count(),
                'goodMetaBooks' => Book::published()->count() - $missingMetaBooks->count(),
            ],
        ]);
    }
}
