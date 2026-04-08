<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth; // Auth::id()
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        if (!$userId) {
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
            'allGenres'     => Genre::orderBy('name')->get(['id', 'name', 'slug']),
            'allCategories' => Category::orderBy('name')->get(['id', 'name']), // 🆕
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        // normalize (legacy CSV field গুলো থাকলে trim)
        $request->merge([
            'title'            => trim((string) $request->input('title')),
            'body'             => (string) $request->input('body'),
            'categories'       => (string) $request->input('categories', ''), // legacy (optional)
            'genres'           => (string) $request->input('genres', ''),     // legacy (optional)
            'meta_title'       => trim((string) $request->input('meta_title')),
            'meta_description' => trim((string) $request->input('meta_description')),
        ]);

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'body'             => ['required', 'string'],
            'cover'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],

            // 🆕 preferred (multi-select)
            'genre_ids'        => ['nullable', 'array'],
            'genre_ids.*'      => ['integer', 'exists:genres,id'],
            'category_ids'     => ['nullable', 'array'],
            'category_ids.*'   => ['integer', 'exists:categories,id'],

            // legacy CSV (still accepted)
            'categories'       => ['nullable', 'string', 'max:500'],
            'genres'           => ['nullable', 'string', 'max:500'],
        ]);

        $userId = optional($request->user())->id ?: (app()->environment('local') ? 1 : null);
        if (!$userId) abort(401, 'Login required');

        $coverPath = null;

        DB::beginTransaction();
        try {
            // Slug generation (unique)
            $base = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $data['title']), '-') ?: 'post';
            $slug = $base;
            $i = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $base . '-' . Str::random(4) . ($i > 1 ? $i : '');
                $i++;
            }

            // Reading time & excerpt
            $plain = trim(strip_tags($data['body']));
            $wordsArray = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
            $reading = max(1, (int) ceil((is_array($wordsArray) ? count($wordsArray) : 0) / 200));
            $excerpt = mb_substr($plain, 0, 160) ?: null;

            if ($request->hasFile('cover')) {
                $coverPath = $request->file('cover')->store('covers', 'public');
            }

            $meta = array_filter([
                'title'       => $data['meta_title'] ?? null,
                'description' => $data['meta_description'] ?? null,
            ]);

            $post = Post::create([
                'user_id'      => $userId,
                'title'        => $data['title'],
                'slug'         => $slug,
                'excerpt'      => $excerpt,
                'body'         => $data['body'],
                'cover'        => $coverPath,
                'reading_time' => $reading,
                'status'       => 'draft',
                'published_at' => null,
                'meta_json'    => $meta ?: null,
            ]);

            // 🆕 Sync relations (prefer arrays)
            $categoryIds = $data['category_ids'] ?? [];
            $genreIds    = $data['genre_ids'] ?? [];

            // যদি নতুন array না আসে, তখন CSV fallback
            if (empty($categoryIds) && !empty($data['categories'])) {
                $this->syncCategoriesFromCsv($post, $data['categories']);
            } else {
                $post->categories()->sync($categoryIds);
            }

            if (empty($genreIds) && !empty($data['genres'])) {
                $this->syncGenresFromCsv($post, $data['genres']);
            } else {
                $post->genres()->sync($genreIds);
            }

            DB::commit();

            Cache::forget('rss.items');
            Cache::forget('sitemap.xml');

            return redirect()
                ->route('dashboard.posts.index')
                ->with('success', 'Draft created!');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($coverPath)) {
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
        $selectedGenreIds    = $post->genres()->pluck('id')->all();

        $meta = (array) ($post->meta_json ?? []);

        return Inertia::render('Dashboard/Posts/Edit', [
            'post' => [
                'id'                   => $post->id,
                'title'                => $post->title,
                'body'                 => $post->body,
                'status'               => $post->status,
                'published_at'         => optional($post->published_at)?->format('Y-m-d'),
                'cover_url'            => $post->cover ? asset('storage/' . $post->cover) : null,
                'meta_title'           => $meta['title'] ?? '',
                'meta_description'     => $meta['description'] ?? '',
                // multi-select selected ids
                'selected_category_ids'=> $selectedCategoryIds,
                'selected_genre_ids'   => $selectedGenreIds,
            ],
            'allGenres'     => Genre::orderBy('name')->get(['id', 'name', 'slug']),
            'allCategories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        // normalize (legacy CSV)
        $request->merge([
            'title'            => trim((string) $request->input('title')),
            'body'             => (string) $request->input('body'),
            'categories'       => (string) $request->input('categories', ''), // legacy optional
            'genres'           => (string) $request->input('genres', ''),     // legacy optional
            'meta_title'       => trim((string) $request->input('meta_title')),
            'meta_description' => trim((string) $request->input('meta_description')),
        ]);

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'body'             => ['required', 'string'],
            'status'           => ['required', 'in:draft,published,scheduled'],
            'published_at'     => ['nullable', 'date'],
            'cover'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],

            // 🆕 preferred (multi-select)
            'genre_ids'        => ['nullable', 'array'],
            'genre_ids.*'      => ['integer', 'exists:genres,id'],
            'category_ids'     => ['nullable', 'array'],
            'category_ids.*'   => ['integer', 'exists:categories,id'],

            // legacy CSV fallback
            'categories'       => ['nullable', 'string', 'max:500'],
            'genres'           => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $plain = trim(strip_tags($data['body']));
            $wordsArray = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
            $reading = max(1, (int) ceil((is_array($wordsArray) ? count($wordsArray) : 0) / 200));
            $excerpt = mb_substr($plain, 0, 160) ?: null;

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $newCover = $post->cover;
            if ($request->hasFile('cover')) {
                if ($post->cover) {
                    Storage::disk('public')->delete($post->cover);
                }
                $newCover = $request->file('cover')->store('covers', 'public');
            }

            $meta = array_filter([
                'title'       => $data['meta_title'] ?? null,
                'description' => $data['meta_description'] ?? null,
            ]);

            $post->update([
                'title'        => $data['title'],
                'body'         => $data['body'],
                'excerpt'      => $excerpt,
                'reading_time' => $reading,
                'status'       => $data['status'],
                'published_at' => $data['published_at'],
                'cover'        => $newCover,
                'meta_json'    => $meta ?: null,
            ]);

            // 🆕 Sync relations (prefer arrays)
            $categoryIds = $data['category_ids'] ?? [];
            $genreIds    = $data['genre_ids'] ?? [];

            if (empty($categoryIds) && array_key_exists('categories', $data)) {
                // যদি form থেকে category_ids না আসে, কিন্তু legacy CSV আসে
                $this->syncCategoriesFromCsv($post, $data['categories'] ?? '');
            } else {
                $post->categories()->sync($categoryIds);
            }

            if (empty($genreIds) && array_key_exists('genres', $data)) {
                $this->syncGenresFromCsv($post, $data['genres'] ?? '');
            } else {
                $post->genres()->sync($genreIds);
            }

            DB::commit();

            Cache::forget('rss.items');
            Cache::forget('sitemap.xml');

            return redirect()->route('dashboard.posts.index')->with('success', 'Post updated!');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Publish/Unpublish toggle
     */
    public function toggle(Post $post)
    {
        $this->authorize('update', $post);

        $makePublished = $post->status !== 'published';

        $post->status       = $makePublished ? 'published' : 'draft';
        $post->published_at = $makePublished ? ($post->published_at ?: now()) : null;
        $post->save();

        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        if (request()->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'status'  => $post->status,
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

            Cache::forget('rss.items');
            Cache::forget('sitemap.xml');

            return back()->with('success', 'Post deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Helper: sync genres from CSV string (legacy fallback)
     */
    private function syncGenresFromCsv(Post $post, string $csv): void
    {
        $raw = preg_split('/[,\x{FF0C}\x{060C};|]+/u', $csv);
        $names = collect($raw ?: [])
            ->map(fn($s) => trim(preg_replace('/\s+/u', ' ', (string) $s)))
            ->filter()
            ->unique(fn($s) => mb_strtolower($s, 'UTF-8'))
            ->values();

        if ($names->isEmpty()) {
            $post->genres()->sync([]);
            return;
        }

        $ids = [];
        foreach ($names as $name) {
            $slugBase = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $name), '-') ?: Str::random(6);

            $existing = Genre::where('slug', $slugBase)->first();
            if (!$existing) {
                $existing = Genre::create(['name' => $name, 'slug' => $slugBase]);
            }
            $ids[] = $existing->id;
        }

        $post->genres()->sync($ids);
    }

    /**
     * Helper: sync categories from CSV string (legacy fallback)
     */
    private function syncCategoriesFromCsv(Post $post, string $csv): void
    {
        $raw = preg_split('/[,\x{FF0C}\x{060C};|]+/u', $csv);
        $names = collect($raw ?: [])
            ->map(fn($s) => trim(preg_replace('/\s+/u', ' ', (string) $s)))
            ->filter()
            ->unique(fn($s) => mb_strtolower($s, 'UTF-8'))
            ->values();

        if ($names->isEmpty()) {
            $post->categories()->sync([]);
            return;
        }

        $ids = [];
        foreach ($names as $name) {
            $slugBase = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $name), '-') ?: Str::random(6);

            $existing = Category::where('slug', $slugBase)->first();
            if (!$existing) {
                $existing = Category::create(['name' => $name, 'slug' => $slugBase]);
            }
            $ids[] = $existing->id;
        }

        $post->categories()->sync($ids);
    }
}
