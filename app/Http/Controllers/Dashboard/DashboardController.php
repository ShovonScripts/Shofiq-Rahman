<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

// Models
use App\Models\User;
use App\Models\Post;
use App\Models\Book;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\HomeBanner;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Top-line stats
        $stats = [
            'posts'         => Post::count(),
            'books'         => Book::count(),
            'users'         => User::count(),
            'comments'      => Comment::count(),
            'messages'      => ContactMessage::count(),
            // If your Post model has a `status` column:
            'pending_posts' => Post::whereIn('status', ['draft', 'pending'])->count(),
        ];

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

        // ── Homepage summary card
        $homepage = [
            'banners_total'  => HomeBanner::count(),
            'banners_active' => HomeBanner::where('is_active', true)->count(),
            'author'         => [
                'name'      => Setting::get('home_author_name', ''),
                'has_photo' => (bool) Setting::get('home_author_photo'),
            ],
        ];

        // ── Role flags (UI conveniences)
        $u = auth()->user();
        $roleFlags = [
            'is_admin'         => $u && method_exists($u, 'isAdmin')  ? $u->isAdmin()  : false,
            'is_author'        => $u && method_exists($u, 'isAuthor') ? $u->isAuthor() : false,
            // Example: only admins can manage users
            'can_manage_users' => $u && method_exists($u, 'isAdmin')  ? $u->isAdmin()  : false,
        ];

        return Inertia::render('Dashboard/Index', [
            'stats'     => $stats,
            'recent'    => $recent,
            'homepage'  => $homepage,
            'roleFlags' => $roleFlags,
        ]);
    }
}
