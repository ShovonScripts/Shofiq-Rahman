<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
// Models
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\HomeBanner;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Top-line stats (cached for 60s to avoid 6 separate count() on every dashboard visit)
        $stats = Cache::remember('dashboard.stats', now()->addSeconds(60), function () {
            return [
                'posts' => Post::count(),
                'books' => Book::count(),
                'users' => User::count(),
                'comments' => Comment::count(),
                'messages' => ContactMessage::count(),
                'pending_posts' => Post::whereIn('status', ['draft', 'pending'])->count(),
            ];
        });

        // ── Recent items (lightweight column selection)
        $recent = [
            'posts' => Post::orderByDesc('id')
                ->take(6)
                ->get(['id', 'title', 'slug', 'status', 'created_at']), // keep 'status' only if it exists

            'books' => Book::orderByDesc('id')
                ->take(6)
                ->get(['id', 'title', 'slug', 'created_at']), // avoid 'status' unless present on books table

            'comments' => Comment::orderByDesc('id')
                ->with('user:id,name') // uses Comment::user() relation you added
                ->take(6)
                ->get(['id', 'user_id', 'commentable_type', 'commentable_id', 'created_at']),

            'messages' => ContactMessage::orderByDesc('id')
                ->take(6)
                ->get(['id', 'name', 'email', 'subject', 'created_at']),
        ];

        // ── Homepage summary card (cached for 60s)
        $homepage = Cache::remember('dashboard.homepage', now()->addSeconds(60), function () {
            return [
                'banners_total' => HomeBanner::count(),
                'banners_active' => HomeBanner::where('is_active', true)->count(),
                'author' => [
                    'name' => Setting::get('home_author_name', ''),
                    'has_photo' => (bool) Setting::get('home_author_photo'),
                ],
            ];
        });

        // ── Role flags (UI conveniences)
        $u = auth()->user();
        $roleFlags = [
            'is_admin' => $u?->isAdmin() ?? false,
            'is_author' => $u?->isAuthor() ?? false,
            'can_manage_users' => $u?->isAdmin() ?? false,
        ];

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recent' => $recent,
            'homepage' => $homepage,
            'roleFlags' => $roleFlags,
        ]);
    }
}
