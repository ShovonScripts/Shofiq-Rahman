<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Post;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * 📰 Blog Index: Published পোস্ট লিস্ট (with search + genre filter)
     * Route: GET /blog
     */
    public function index()
    {
        $q = trim((string) request('q', ''));
        $genres = request()->input('genre', []);

        if (is_string($genres) && $genres !== '') {
            $genres = [$genres];
        } elseif (! is_array($genres)) {
            $genres = [];
        }
        $genres = array_filter(array_map('trim', $genres));

        $posts = Post::with(['categories:id,name,slug', 'genres:id,name,slug'])
            ->published()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($s) use ($q) {
                    $s->where('title', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%");
                });
            })
            ->when(! empty($genres), function ($query) use ($genres) {
                $query->whereHas('genres', fn ($g) => $g->whereIn('slug', $genres));
            })
            ->recent()
            ->select('id', 'title', 'slug', 'excerpt', 'reading_time', 'published_at', 'cover')
            ->paginate(10)
            ->appends(['q' => $q, 'genre' => $genres])
            ->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'reading_time' => $p->reading_time,
                'published_at' => optional($p->published_at)->toDateString(),
                'published_iso' => optional($p->published_at)->toAtomString(),
                'cover_url' => $p->cover_url,
                'categories' => $p->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                'genres' => $p->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
            ]);

        $allGenres = Cache::remember(CacheKeys::allGenres(), now()->addMinutes(30), fn () => Genre::orderBy('name')->get(['name', 'slug'])
        );

        // ✅ Home নয়, এখন Blog পেজ রেন্ডার করব
        return Inertia::render('Blog', [
            'posts' => $posts,
            'filters' => ['q' => $q, 'genre' => $genres],
            'allGenres' => $allGenres,
        ]);
    }

    /**
     * 📰 Single post: /lekha/{slug} (with latest 50 comments + likes info)
     */
    public function show(string $slug)
    {
        $post = Post::published()
            ->with([
                'categories:id,name,slug',
                'author:id,name',
                'genres:id,name,slug',
                'comments' => fn ($q) => $q->with('author:id,name')->latest()->limit(50),
            ])
            ->withCount('likes')
            ->where('slug', $slug)
            ->firstOrFail(['id', 'user_id', 'title', 'slug', 'body', 'reading_time', 'published_at', 'cover', 'meta_json']);

        $likedByMe = auth()->check()
            ? $post->likes()->where('user_id', auth()->id())->exists()
            : false;

        return Inertia::render('PostShow', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'body' => $post->body,
                'reading_time' => $post->reading_time,
                'published_at' => optional($post->published_at)->toDateString(),
                'published_iso' => optional($post->published_at)->toAtomString(),
                'author' => $post->author?->name,
                'cover_url' => $post->cover_url,
                'meta' => $post->meta_json ?: [],
                'categories' => $post->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                'genres' => $post->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
                'likes_count' => (int) $post->likes_count,
                'liked_by_me' => $likedByMe,
                'comments' => $post->comments->map(fn ($c) => [
                    'id' => $c->id,
                    'body' => $c->body,
                    'author' => $c->author?->name,
                    'user_id' => $c->user_id,
                    'time' => $c->created_at->diffForHumans(),
                ]),
            ],
        ]);
    }

    /**
     * 📂 All Categories: /categories
     */
    public function categoriesIndex()
    {
        $categories = Cache::remember(CacheKeys::categoriesWithPostCount(), now()->addMinutes(15), fn () => Category::withCount(['posts' => function ($q) {
            $q->published();
        }])
            ->orderBy('name')
            ->select('id', 'name', 'slug', 'updated_at')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'post_count' => (int) $c->posts_count,
            ])
        );

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * 📂 By Category: /category/{slug}
     */
    public function byCategory(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail(['id', 'name', 'slug', 'updated_at']);

        $posts = $category->posts()
            ->published()
            ->with('categories:id,name,slug', 'genres:id,name,slug')
            ->select('posts.id', 'title', 'slug', 'excerpt', 'reading_time', 'published_at', 'cover')
            ->recent()
            ->paginate(10)
            ->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'reading_time' => $p->reading_time,
                'published_at' => optional($p->published_at)->toDateString(),
                'cover_url' => $p->cover_url,
                'categories' => $p->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                'genres' => $p->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
            ]);

        return Inertia::render('CategoryShow', [
            'category' => ['name' => $category->name, 'slug' => $category->slug],
            'posts' => $posts,
        ]);
    }

    /**
     * 🎭 By Genre: /genre/{slug}
     */
    public function byGenre(string $slug)
    {
        $genre = Genre::where('slug', $slug)->firstOrFail(['id', 'name', 'slug']);

        $posts = Post::with(['categories:id,name,slug', 'genres:id,name,slug'])
            ->published()
            ->whereHas('genres', fn ($q) => $q->where('slug', $slug))
            ->select('id', 'title', 'slug', 'excerpt', 'reading_time', 'published_at', 'cover')
            ->recent()
            ->paginate(10)
            ->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'reading_time' => $p->reading_time,
                'published_at' => optional($p->published_at)->toDateString(),
                'cover_url' => $p->cover_url,
                'categories' => $p->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                'genres' => $p->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
            ]);

        return Inertia::render('GenreShow', [
            'genre' => ['name' => $genre->name, 'slug' => $genre->slug],
            'posts' => $posts,
        ]);
    }

    /**
     * 🔍 Search: /search?q=... (+ optional genre filter)
     */
    public function search()
    {
        $q = (string) request('q', '');
        $genres = request()->input('genre', []);

        if (is_string($genres) && $genres !== '') {
            $genres = [$genres];
        } elseif (! is_array($genres)) {
            $genres = [];
        }
        $genres = array_filter(array_map('trim', $genres));

        $posts = Post::query()
            ->published()
            ->with('genres:id,name,slug')
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$q}%")
                ->orWhere('excerpt', 'like', "%{$q}%")))
            ->when(! empty($genres), fn ($query) => $query->whereHas('genres', fn ($g) => $g->whereIn('slug', $genres)))
            ->select('id', 'title', 'slug', 'excerpt', 'reading_time', 'published_at', 'cover')
            ->recent()
            ->paginate(10)
            ->appends(['q' => $q, 'genre' => $genres])
            ->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'reading_time' => $p->reading_time,
                'published_at' => optional($p->published_at)->toDateString(),
                'cover_url' => $p->cover_url,
                'genres' => $p->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
            ]);

        $allGenres = Cache::remember(CacheKeys::allGenres(), now()->addMinutes(30), fn () => Genre::orderBy('name')->get(['name', 'slug'])
        );

        return Inertia::render('Search', [
            'q' => $q,
            'posts' => $posts,
            'filters' => ['q' => $q, 'genre' => $genres],
            'allGenres' => $allGenres,
        ]);
    }
}
