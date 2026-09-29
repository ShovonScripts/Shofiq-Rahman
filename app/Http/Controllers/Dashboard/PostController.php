<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Post;
use App\Services\ReadingTimeService;
use App\Services\SlugService;
use App\Services\TermSyncService;
use App\Support\CacheKeys;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Apply middleware: auth + EnsureAdmin
     */
    public function __construct()
    {
        // NOTE: এখন শুধুই admin ঢুকতে পারবে (EnsureAdmin)। author-দের ঢোকাতে চাইলে
        // এই middleware টি কাস্টমাইজ করতে হবে।
        $this->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class]);
    }

    public function index()
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(401, 'Login required');
        }

        $posts = Post::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->select('id', 'title', 'slug', 'status', 'published_at', 'created_at')
            ->paginate(10);

        return Inertia::render('Dashboard/Posts/Index', ['posts' => $posts]);
    }

    public function create()
    {
        $this->authorize('create', Post::class);

        return Inertia::render('Dashboard/Posts/Create', [
            'allGenres' => Genre::orderBy('name')->get(['id', 'name', 'slug']),
            'allCategories' => Category::orderBy('name')->get(['id', 'name']), // 🆕
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        // normalize (legacy CSV field গুলো থাকলে trim)
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'body' => (string) $request->input('body'),
            'categories' => (string) $request->input('categories', ''), // legacy (optional)
            'genres' => (string) $request->input('genres', ''),     // legacy (optional)
            'meta_title' => trim((string) $request->input('meta_title')),
            'meta_description' => trim((string) $request->input('meta_description')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],

            // 🆕 preferred (multi-select)
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            // legacy CSV (still accepted)
            'categories' => ['nullable', 'string', 'max:500'],
            'genres' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = optional($request->user())->id ?: (app()->environment('local') ? 1 : null);
        if (! $userId) {
            abort(401, 'Login required');
        }

        $coverPath = null;

        DB::beginTransaction();
        try {
            $slugService = new SlugService;
            $readingTimeService = new ReadingTimeService;
            $termSyncService = new TermSyncService;

            $slug = $slugService->generateUniqueSlug(new Post, $data['title']);

            $plain = trim(strip_tags($data['body']));
            $reading = $readingTimeService->calculate($plain);
            $excerpt = mb_substr($plain, 0, 160) ?: null;

            if ($request->hasFile('cover')) {
                $coverPath = $request->file('cover')->store('covers', 'public');
            }

            $meta = array_filter([
                'title' => $data['meta_title'] ?? null,
                'description' => $data['meta_description'] ?? null,
            ]);

            $post = Post::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $excerpt,
                'body' => $data['body'],
                'cover' => $coverPath,
                'reading_time' => $reading,
                'status' => 'draft',
                'published_at' => null,
                'meta_json' => $meta ?: null,
            ]);

            $categoryIds = $data['category_ids'] ?? [];
            $genreIds = $data['genre_ids'] ?? [];

            if (empty($categoryIds) && ! empty($data['categories'])) {
                $termSyncService->syncCategories($post, $data['categories']);
            } else {
                $post->categories()->sync($categoryIds);
            }

            if (empty($genreIds) && ! empty($data['genres'])) {
                $termSyncService->syncGenres($post, $data['genres']);
            } else {
                $post->genres()->sync($genreIds);
            }

            DB::commit();

            Cache::forget(CacheKeys::latestPosts());
            Cache::forget(CacheKeys::categoriesWithPostCount());
            Cache::forget('rss.items');
            Cache::forget('sitemap.xml');

            return redirect()
                ->route('dashboard.posts.index')
                ->with('success', 'Draft created!');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (! empty($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            throw $e;
        }
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        // selected ids (multi-select-এর জন্য)
        $selectedCategoryIds = $post->categories()->pluck('id')->all();
        $selectedGenreIds = $post->genres()->pluck('id')->all();

        $meta = (array) ($post->meta_json ?? []);

        return Inertia::render('Dashboard/Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'status' => $post->status,
                'published_at' => optional($post->published_at)->format('Y-m-d\TH:i'),
                'cover_url' => $post->cover ? storage_url($post->cover) : null,
                'meta_title' => $meta['title'] ?? '',
                'meta_description' => $meta['description'] ?? '',
                // multi-select selected ids
                'selected_category_ids' => $selectedCategoryIds,
                'selected_genre_ids' => $selectedGenreIds,
            ],
            'allGenres' => Genre::orderBy('name')->get(['id', 'name', 'slug']),
            'allCategories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        // normalize (legacy CSV)
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'body' => (string) $request->input('body'),
            'meta_title' => trim((string) $request->input('meta_title')),
            'meta_description' => trim((string) $request->input('meta_description')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['required_if:status,scheduled', 'nullable', 'date'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],

            // 🆕 preferred (multi-select)
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            // legacy CSV fallback
            'categories' => ['nullable', 'string', 'max:500'],
            'genres' => ['nullable', 'string', 'max:500'],
        ]);

        $oldCover = $post->cover;
        $newCover = $oldCover;

        DB::beginTransaction();
        try {
            $readingTimeService = new ReadingTimeService;
            $termSyncService = new TermSyncService;

            $plain = trim(strip_tags($data['body']));
            $reading = $readingTimeService->calculate($plain);
            $excerpt = mb_substr($plain, 0, 160) ?: null;

            $publishedAt = $data['published_at'] ?? $post->published_at;
            if ($data['status'] === 'published' && ! $publishedAt) {
                $publishedAt = now();
            } elseif ($data['status'] === 'draft') {
                $publishedAt = null;
            }

            if ($request->hasFile('cover')) {
                $newCover = $request->file('cover')->store('covers', 'public');
            }

            $meta = array_filter([
                'title' => $data['meta_title'] ?? null,
                'description' => $data['meta_description'] ?? null,
            ]);

            $post->update([
                'title' => $data['title'],
                'body' => $data['body'],
                'excerpt' => $excerpt,
                'reading_time' => $reading,
                'status' => $data['status'],
                'published_at' => $publishedAt,
                'cover' => $newCover,
                'meta_json' => $meta ?: null,
            ]);

            // An omitted taxonomy is unchanged; an explicit empty selection clears it.
            if (array_key_exists('category_ids', $data)) {
                $post->categories()->sync($data['category_ids'] ?? []);
            } elseif (array_key_exists('categories', $data)) {
                $termSyncService->syncCategories($post, $data['categories'] ?? '');
            }

            if (array_key_exists('genre_ids', $data)) {
                $post->genres()->sync($data['genre_ids'] ?? []);
            } elseif (array_key_exists('genres', $data)) {
                $termSyncService->syncGenres($post, $data['genres'] ?? '');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($newCover && $newCover !== $oldCover) {
                Storage::disk('public')->delete($newCover);
            }
            throw $e;
        }

        if ($oldCover && $newCover !== $oldCover) {
            Storage::disk('public')->delete($oldCover);
        }

        Cache::forget(CacheKeys::latestPosts());
        Cache::forget(CacheKeys::categoriesWithPostCount());
        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        return redirect()->route('dashboard.posts.index')->with('success', 'Post updated!');
    }

    /**
     * Publish/Unpublish toggle
     */
    public function toggle(Post $post)
    {
        $this->authorize('update', $post);

        $makePublished = $post->status !== 'published';

        $post->status = $makePublished ? 'published' : 'draft';
        $post->published_at = $makePublished ? ($post->published_at ?: now()) : null;
        $post->save();

        Cache::forget(CacheKeys::latestPosts());
        Cache::forget(CacheKeys::categoriesWithPostCount());
        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        if (request()->wantsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $post->status,
                'message' => $makePublished ? 'Post published.' : 'Post unpublished.',
            ]);
        }

        return back()->with('success', $makePublished ? 'Post published.' : 'Post unpublished.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        DB::beginTransaction();
        try {
            if ($post->cover) {
                Storage::disk('public')->delete($post->cover);
            }

            // detach relations
            $post->categories()->sync([]);
            $post->genres()->sync([]);
            $post->comments()->delete();
            $post->likes()->delete();

            $post->delete();

            DB::commit();

            Cache::forget(CacheKeys::latestPosts());
            Cache::forget(CacheKeys::categoriesWithPostCount());
            Cache::forget('rss.items');
            Cache::forget('sitemap.xml');

            return back()->with('success', 'Post deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
