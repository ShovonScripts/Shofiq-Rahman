<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    public function index()
    {
        // সাইট URL (fallback সহ)
        $siteUrl = rtrim(config('app.url') ?: url('/'), '/');

        // 60 মিনিট ক্যাশ
        $items = Cache::remember('rss.items', now()->addMinutes(60), function () {
            // যদি scopePublished()/scopeRecent() থাকে, সেগুলো ব্যবহার করো
            return Post::published()
                ->recent()
                ->select('id', 'title', 'slug', 'excerpt', 'published_at', 'updated_at')
                ->limit(50)
                ->get();
        });

        return response()
            ->view('rss', [
                'siteUrl' => $siteUrl,
                'items' => $items,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
