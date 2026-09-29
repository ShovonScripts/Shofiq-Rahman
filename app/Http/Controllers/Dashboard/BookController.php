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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'language' => ['nullable', 'string', 'max:10'],
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

        $slugService = new SlugService;
        $slug = (string) ($validated['slug'] ?? '');
        if ($slug === '') {
            $slug = $slugService->generateUniqueSlug(new Book, $validated['title']);
        } else {
            $slug = $slugService->generateUniqueSlug(new Book, $slug);
        }

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('books/covers', 'public');
        }

        try {
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

                $termSyncService = new TermSyncService;

                if ($request->has('genre_ids') && is_array($request->input('genre_ids'))) {
                    $book->genres()->sync($request->input('genre_ids'));
                } else {
                    $termSyncService->syncGenres($book, (string) ($validated['genres'] ?? ''));
                }
                $termSyncService->syncCategories($book, (string) ($validated['categories'] ?? ''));

                $this->syncPurchaseLinks($book, $validated['links'] ?? $request->input('links', []));
            });
        } catch (\Throwable $e) {
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }
            throw $e;
        }

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

        $request->merge(['title' => trim((string) $request->input('title'))]);
        if ($request->filled('slug') && is_string($request->input('slug'))) {
            // Validate the final slug, not a value that changes after validation.
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('books', 'slug')->ignore($book->id)],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'language' => ['nullable', 'string', 'max:10'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'release_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'genres' => ['nullable', 'string', 'max:500'],
            'categories' => ['nullable', 'string', 'max:500'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'links' => ['nullable', 'array'],
            'links.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('book_links', 'id')->where('book_id', $book->id)],
            'links.*.label' => ['nullable', 'string', 'max:100'],
            'links.*.url' => ['nullable', 'string', 'max:2048'],
            'links.*.is_primary' => ['nullable', 'boolean'],
            'links.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $oldCover = $book->cover;
        $coverPath = $request->hasFile('cover')
            ? $request->file('cover')->store('books/covers', 'public')
            : $oldCover;

        try {
            DB::transaction(function () use ($validated, $book, $coverPath) {
                $publishedAt = array_key_exists('published_at', $validated)
                    ? $validated['published_at'] : $book->published_at;

                // Preserve optional fields that older edit forms do not submit.
                // Explicit null still lets a client clear a field deliberately.
                $book->fill(Arr::only($validated, [
                    'title', 'subtitle', 'description', 'isbn', 'language',
                    'pages', 'release_date', 'status',
                ]));
                $book->fill([
                    'slug' => ($validated['slug'] ?? null) ?: $book->slug,
                    'cover' => $coverPath,
                    'published_at' => $validated['status'] === 'published' ? ($publishedAt ?: now()) : null,
                ])->save();

                $termSyncService = new TermSyncService;
                if (array_key_exists('genre_ids', $validated)) {
                    $book->genres()->sync($validated['genre_ids'] ?? []);
                } elseif (array_key_exists('genres', $validated)) {
                    $termSyncService->syncGenres($book, $validated['genres'] ?? '');
                }
                if (array_key_exists('category_ids', $validated)) {
                    $book->categories()->sync($validated['category_ids'] ?? []);
                } elseif (array_key_exists('categories', $validated)) {
                    $termSyncService->syncCategories($book, $validated['categories'] ?? '');
                }
                if (array_key_exists('links', $validated)) {
                    $this->syncPurchaseLinks($book, $validated['links'] ?? []);
                }
            });
        } catch (\Throwable $e) {
            if ($coverPath && $coverPath !== $oldCover) {
                Storage::disk('public')->delete($coverPath);
            }
            throw $e;
        }

        if ($oldCover && $coverPath !== $oldCover) {
            Storage::disk('public')->delete($oldCover);
        }

        Cache::forget(CacheKeys::latestBooks());
        Cache::forget(CacheKeys::allCategories());
        Cache::forget(CacheKeys::allGenres());
        Cache::forget('rss.items');
        Cache::forget('sitemap.xml');

        return redirect()->route('dashboard.books.edit', $book->id)
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

    /** Update links in place so stable redirect URLs and click history survive edits. */
    private function syncPurchaseLinks(Book $book, array $links): void
    {
        $existing = $book->purchaseLinks()->get()->keyBy('id');
        $keepIds = [];
        $sawPrimary = false;

        foreach ($links as $index => $attributes) {
            $url = trim((string) ($attributes['url'] ?? ''));
            if ($url === '' && empty($attributes['id'])) {
                continue;
            }

            if ($url !== '' && ! preg_match('~^[a-z][a-z0-9+.-]*:~i', $url)) {
                $url = 'https://'.ltrim($url, '/');
            }
            if (mb_strlen($url) > 2048 || ! filter_var($url, FILTER_VALIDATE_URL)
                || ! in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                throw ValidationException::withMessages(["links.{$index}.url" => 'Enter a valid HTTP or HTTPS purchase URL.']);
            }

            if (! empty($attributes['id'])) {
                $link = $existing->get((int) $attributes['id']);
                abort_unless($link, 404);
            } else {
                // Backward compatibility for clients that did not send link IDs.
                $link = $existing->first(fn (BookLink $item) => $item->url === $url && ! in_array($item->id, $keepIds, true));
                $link ??= $book->purchaseLinks()->make();
            }

            $isPrimary = ! empty($attributes['is_primary']) && ! $sawPrimary;
            $sawPrimary = $sawPrimary || $isPrimary;
            $link->fill([
                'label' => trim((string) ($attributes['label'] ?? '')) ?: 'Buy',
                'url' => $url,
                'is_primary' => $isPrimary,
                'sort_order' => $attributes['sort_order'] ?? $index,
            ])->save();
            $keepIds[] = $link->id;
        }

        $book->purchaseLinks()->whereNotIn('id', $keepIds)->delete();
    }
}
