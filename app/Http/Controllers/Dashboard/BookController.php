<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLink;
use App\Models\Category;
use App\Models\Genre;
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

class BookController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class]);
    }

    public function index()
    {
        $books = Book::where('user_id', Auth::id())
            ->with('author:id,name')
            ->latest()
            ->paginate(15)
            ->through(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'slug' => $b->slug,
                'subtitle' => $b->subtitle,
                'status' => $b->status,
                'cover_url' => $b->cover_url,
                'published_at' => optional($b->published_at)->toDateString(),
                'author_name' => $b->author->name ?? 'Unknown',
            ]);

        return Inertia::render('Dashboard/Books/Index', ['books' => $books]);
    }

    public function create()
    {
        $this->authorize('create', Book::class);

        $genres = Genre::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Dashboard/Books/Create', [
            'genres' => $genres,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Book::class);

        // normalize
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'published_at' => $request->filled('published_at') ? $request->input('published_at') : null,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:books,slug'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'language' => ['nullable', 'string', 'max:16'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'release_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],

            // relations (আপনি CSV পাঠাচ্ছেন)
            'genres' => ['nullable', 'string', 'max:500'],
            'categories' => ['nullable', 'string', 'max:500'],
            'genre_ids' => ['sometimes', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],

            // ✅ NEW: links validation
            'links' => ['sometimes', 'array'],
            'links.*.label' => ['nullable', 'string', 'max:100'],
            'links.*.url' => ['nullable', 'string', 'max:2048'],
            'links.*.is_primary' => ['nullable', 'boolean'],
            'links.*.sort_order' => ['nullable', 'integer'],
        ]);

        $slugService = new SlugService();
        $slug = (string) ($validated['slug'] ?? '');
        if ($slug === '') {
            $slug = $slugService->generateUniqueSlug(new Book(), $validated['title']);
        } else {
            $slug = $slugService->generateUniqueSlug(new Book(), $slug);
        }

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('books/covers', 'public');
        }

        DB::transaction(function () use ($request, $validated, $slug, $coverPath) {
            /** @var Book $book */
            $book = new Book;
            $book->fill([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'slug' => $slug,
                'subtitle' => $validated['subtitle'] ?? null,
                'description' => $validated['description'] ?? null,
                'cover' => $coverPath,
                'isbn' => $validated['isbn'] ?? null,
                'language' => $validated['language'] ?? null,
                'pages' => $validated['pages'] ?? null,
                'release_date' => $validated['release_date'] ?? null,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published'
                                    ? ($validated['published_at'] ?? now())
                                    : null,
            ]);
            $book->save();

            $termSyncService = new TermSyncService();

            if ($request->has('genre_ids') && is_array($request->input('genre_ids'))) {
                $book->genres()->sync($request->input('genre_ids'));
            } else {
                $termSyncService->syncGenres($book, (string) ($validated['genres'] ?? ''));
            }
            $termSyncService->syncCategories($book, (string) ($validated['categories'] ?? ''));

            $this->syncPurchaseLinks($book, $validated['links'] ?? $request->input('links', []));
        });

        Cache::forget(CacheKeys::latestBooks());
        Cache::forget(CacheKeys::allCategories());
        Cache::forget(CacheKeys::allGenres());
        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        return redirect()->route('dashboard.books.index')
            ->with('success', 'Book created successfully!');
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $book->load([
            // ordered links + click count
            'links' => fn ($q) => $q->withCount('clicks')->orderByRaw('COALESCE(sort_order, position, 0) ASC, id ASC'),
            'genres:id,name,slug',
            'categories:id,name,slug',
        ]);

        $genresList = Genre::orderBy('name')->get(['id', 'name']);
        $categoriesList = Category::orderBy('name')->get(['id', 'name']);

        $genresCsv = $book->genres->pluck('name')->implode(', ');
        $categoriesCsv = $book->categories->pluck('name')->implode(', ');
        $genreIds = $book->genres->pluck('id')->values();
        $categoryIds = $book->categories->pluck('id')->values();

        return Inertia::render('Dashboard/Books/Edit', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'slug' => $book->slug,
                'subtitle' => $book->subtitle,
                'description' => $book->description,
                'cover_url' => $book->cover_url,
                'isbn' => $book->isbn,
                'language' => $book->language,
                'pages' => $book->pages,
                'release_date' => optional($book->release_date)->toDateString(),
                'status' => $book->status,
                'published_at' => optional($book->published_at)->toDateString(),
                'links' => $book->links->map(fn ($link) => [
                    'id' => $link->id,
                    'label' => $link->label,
                    'url' => $link->url,
                    'is_primary' => (bool) $link->is_primary,
                    'sort_order' => (int) $link->sort_order,
                    'click_count' => (int) ($link->clicks_count ?? 0),
                ]),
                // Pre-fill both CSV and raw IDs
                'genres' => $genresCsv,
                'categories' => $categoriesCsv,
                'genre_ids' => $genreIds,
                'category_ids' => $categoryIds,
            ],
            'genres' => $genresList,
            'categories' => $categoriesList,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        // normalize
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'published_at' => $request->filled('published_at') ? $request->input('published_at') : null,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:books,slug,'.$book->id],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'language' => ['nullable', 'string', 'max:16'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'release_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],

            'genres' => ['nullable', 'string', 'max:500'],
            'categories' => ['nullable', 'string', 'max:500'],
            'genre_ids' => ['sometimes', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],

            // ✅ NEW: links validation (update এর ক্ষেত্রেও)
            'links' => ['sometimes', 'array'],
            'links.*.label' => ['nullable', 'string', 'max:100'],
            'links.*.url' => ['nullable', 'string', 'max:2048'],
            'links.*.is_primary' => ['nullable', 'boolean'],
            'links.*.sort_order' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($request, $validated, $book) {
            // slug keep/sanitize
            $slug = $validated['slug'] ?? $book->slug;
            $slug = $slug ? Str::slug($slug) : $book->slug;

            // cover replace
            $coverPath = $book->cover;
            if ($request->hasFile('cover')) {
                if ($coverPath) {
                    Storage::disk('public')->delete($coverPath);
                }
                $coverPath = $request->file('cover')->store('books/covers', 'public');
            }

            // published_at transitions
            $status = $validated['status'];
            $published_at = $validated['published_at']; // null or date

            if ($status === 'published' && $book->status === 'draft' && ! $published_at) {
                $published_at = now();
            }
            if ($status === 'draft') {
                $published_at = null;
            }

            // explicit fill + save
            $book->fill([
                'title' => $validated['title'],
                'slug' => $slug,
                'subtitle' => $validated['subtitle'] ?? null,
                'description' => $validated['description'] ?? null,
                'cover' => $coverPath,
                'isbn' => $validated['isbn'] ?? null,
                'language' => $validated['language'] ?? null,
                'pages' => $validated['pages'] ?? null,
                'release_date' => $validated['release_date'] ?? null,
                'status' => $status,
                'published_at' => $published_at,
            ])->save();

            $termSyncService = new TermSyncService();

            if ($request->has('genre_ids') && is_array($request->input('genre_ids'))) {
                $book->genres()->sync($request->input('genre_ids'));
            } else {
                $termSyncService->syncGenres($book, (string) ($validated['genres'] ?? ''));
            }
            $termSyncService->syncCategories($book, (string) ($validated['categories'] ?? ''));

            $this->syncPurchaseLinks($book, $validated['links'] ?? $request->input('links', []));
        });

        $book->refresh();

        Cache::forget(CacheKeys::latestBooks());
        Cache::forget(CacheKeys::allCategories());
        Cache::forget(CacheKeys::allGenres());
        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        return redirect()
            ->route('dashboard.books.edit', $book->id)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }

        $book->delete();

        Cache::forget(CacheKeys::latestBooks());
        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        return redirect()
            ->route('dashboard.books.index')
            ->with('success', 'Book deleted successfully!');
    }

    /**
     * 🔗 Purchase links re-create strategy
     * - আগের সব ডিলিট
     * - ফর্ম থেকে আসা valid লিংকগুলো insert
     * - scheme ensure (http/https), invalid URL স্কিপ
     * - একাধিক is_primary ⇒ প্রথমটাকে true, বাকিগুলো false
     */
    private function syncPurchaseLinks(Book $book, array $links): void
    {
        // পুরোনোগুলো মুছুন
        $book->purchaseLinks()->delete();

        if (empty($links)) {
            return;
        }

        $payload = [];
        $sawPrimary = false;
        $i = 0;

        foreach ($links as $link) {
            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));

            if ($url === '') {
                continue;
            }

            // ensure scheme
            $lower = strtolower($url);
            if (! str_starts_with($lower, 'http://') && ! str_starts_with($lower, 'https://')) {
                $url = 'https://'.ltrim($url, '/');
            }
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue; // invalid URL skip
            }

            $isPrimary = (bool) ($link['is_primary'] ?? false);
            if ($isPrimary && ! $sawPrimary) {
                $sawPrimary = true;      // keep first primary
            } else {
                $isPrimary = false;      // others forced to false
            }

            $payload[] = [
                'book_id' => $book->id,
                'label' => $label !== '' ? $label : 'Buy',
                'url' => $url,
                'is_primary' => $isPrimary,
                'sort_order' => is_numeric($link['sort_order'] ?? null) ? (int) $link['sort_order'] : $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $i++;
        }

        if ($payload) {
            BookLink::insert($payload);
        }
    }
}
