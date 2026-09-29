<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
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
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $posts = Post::query()->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id));
        $books = Book::query()->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id));
        $comments = Comment::query()->when(! $isAdmin, fn ($q) => $q->whereHasMorph(
            'commentable',
            [Post::class, Book::class],
            fn ($parent) => $parent->where('user_id', $user->id)
        ));

        // Never share an admin's cached payload with an author (or another author).
        $cacheKey = $isAdmin ? 'dashboard.stats.admin' : 'dashboard.stats.author.'.$user->id;
        $stats = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($posts, $books, $comments, $isAdmin) {
            return [
                'posts' => (clone $posts)->count(),
                'books' => (clone $books)->count(),
                'users' => $isAdmin ? User::count() : null,
                'comments' => (clone $comments)->count(),
                'messages' => $isAdmin ? ContactMessage::count() : null,
                'pending_posts' => (clone $posts)->whereIn('status', ['draft', 'scheduled'])->count(),
            ];
        });

        $recent = [
            'posts' => $posts->latest('id')->take(6)->get(['id', 'user_id', 'title', 'slug', 'status', 'created_at']),
            'books' => $books->latest('id')->take(6)->get(['id', 'user_id', 'title', 'slug', 'status', 'created_at']),
            'comments' => $comments->latest('id')->with('user:id,name')->take(6)
                ->get(['id', 'user_id', 'commentable_type', 'commentable_id', 'created_at']),
            'messages' => $isAdmin
                ? ContactMessage::latest('id')->take(6)->get(['id', 'name', 'email', 'subject', 'created_at'])
                : [],
        ];

        $homepage = $isAdmin ? Cache::remember('dashboard.homepage', now()->addSeconds(60), function () {
            return [
                'banners_total' => HomeBanner::count(),
                'banners_active' => HomeBanner::where('is_active', true)->count(),
                'author' => [
                    'name' => Setting::get('home_author_name', ''),
                    'has_photo' => (bool) Setting::get('home_author_photo'),
                ],
            ];
        }) : null;

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recent' => $recent,
            'homepage' => $homepage,
            'roleFlags' => [
                'is_admin' => $isAdmin,
                'is_author' => $user->isAuthor(),
                'can_manage_users' => $isAdmin,
            ],
        ]);
    }
}
