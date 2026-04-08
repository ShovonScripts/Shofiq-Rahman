<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookController extends Controller
{
    /**
     * 📚 Public Books index (filters: search + genre + category + year + language)
     * Route: GET /books
     */
    public function index()
    {
        $q      = trim((string) request('q', ''));
        $year   = trim((string) request('year', ''));
        $lang   = trim((string) request('lang', ''));
        $genres = request()->input('genre', []);
        $cats   = request()->input('category', []);

        // Normalize genres to array of slugs
        if (is_string($genres) && $genres !== '') {
            $genres = [$genres];
        } elseif (!is_array($genres)) {
            $genres = [];
        }
        $genres = array_filter(array_map('trim', $genres));

        // Normalize categories to array of slugs
        if (is_string($cats) && $cats !== '') {
            $cats = [$cats];
        } elseif (!is_array($cats)) {
            $cats = [];
        }
        $cats = array_filter(array_map('trim', $cats));

        $books = Book::query()
            ->published()
            ->recent()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($year !== '', function ($query) use ($year) {
                $query->where(function ($sub) use ($year) {
                    $sub->whereYear('release_date', $year)
                        ->orWhereYear('published_at', $year);
                });
            })
            ->when($lang !== '', fn ($query) => $query->where('language', $lang))
            ->when(!empty($genres), function ($query) use ($genres) {
                $query->whereHas('genres', fn ($g) => $g->whereIn('slug', $genres));
            })
            ->when(!empty($cats), function ($query) use ($cats) {
                $query->whereHas('categories', fn ($c) => $c->whereIn('slug', $cats));
            })
            ->with([
                'genres:id,name,slug',
                'categories:id,name,slug',
            ])
            ->select('id', 'user_id', 'title', 'slug', 'subtitle', 'cover', 'published_at', 'release_date', 'language')
            ->paginate(12)
            ->appends([
                'q'        => $q,
                'year'     => $year,
                'lang'     => $lang,
                'genre'    => $genres,
                'category' => $cats,
            ])
            ->through(function ($b) {
                return [
                    'id'           => $b->id,
                    'title'        => $b->title,
                    'slug'         => $b->slug,
                    'subtitle'     => $b->subtitle,
                    'cover_url'    => $b->cover_url,
                    'language'     => $b->language,
                    'published_at' => optional($b->published_at)->toDateString(),
                    'release_year' => optional($b->release_date)->format('Y') ?? optional($b->published_at)->format('Y'),
                    'genres'       => $b->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
                    'categories'   => $b->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                ];
            });

        // Distinct languages for filter
        $langs = Book::published()
            ->whereNotNull('language')
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        // Distinct years (release_date or published_at)
        $years = Book::published()
            ->selectRaw('COALESCE(YEAR(release_date), YEAR(published_at)) as y')
            ->where(function ($q) {
                $q->whereNotNull('release_date')
                  ->orWhereNotNull('published_at');
            })
            ->orderByDesc('y')
            ->distinct()
            ->pluck('y');

        $allGenres     = Genre::orderBy('name')->get(['id', 'name', 'slug']);
        $allCategories = Category::orderBy('name')->get(['id', 'name', 'slug']);

        return Inertia::render('Books/Index', [
            'filters'       => [
                'q'        => $q,
                'year'     => $year,
                'lang'     => $lang,
                'genre'    => $genres,
                'category' => $cats,
            ],
            'books'         => $books,
            'langs'         => $langs,
            'years'         => $years,
            'allGenres'     => $allGenres,
            'allCategories' => $allCategories,
        ]);
    }

    /**
     * 📖 Single Book details page
     * Route: GET /books/{slug}
     */
    public function show(string $slug)
    {
        $book = Book::with([
                'author:id,name',
                // ✅ Ordered links via the scope so they show up consistently
                'links' => fn ($q) => $q->ordered(),
                'genres:id,name,slug',
                'categories:id,name,slug',
                'comments' => fn ($q) => $q->with('author:id,name')->latest()->limit(50),
            ])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $meta = (array) ($book->meta_json ?? []);

        // ✅ Use Auth facade; avoids Intelephense warnings
        $likedByMe = Auth::check()
            ? $book->likes()->where('user_id', Auth::id())->exists()
            : false;

        return Inertia::render('Books/Show', [
            'book' => [
                'id'           => $book->id,
                'title'        => $book->title,
                'slug'         => $book->slug,
                'subtitle'     => $book->subtitle,
                'description'  => $book->description,
                'cover_url'    => $book->cover_url,
                'author'       => $book->author?->name,
                'language'     => $book->language,
                'published_at' => optional($book->published_at)->toDateString(),
                'release_date' => optional($book->release_date)->toDateString(),
                'genres'       => $book->genres->map(fn ($g) => ['name' => $g->name, 'slug' => $g->slug]),
                'categories'   => $book->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                'links'        => $book->links->map(function ($link) {
                    return [
                        'id'         => $link->id,
                        'label'      => $link->label,
                        // Public redirect route (model binding by ID)
                        'url'        => route('books.link.go', ['link' => $link->id]),
                        'is_primary' => (bool) $link->is_primary,
                    ];
                }),
                'meta'         => [
                    'title'       => $meta['title'] ?? null,
                    'description' => $meta['description'] ?? null,
                ],
                'likes_count'  => $book->likes()->count(),
                'liked_by_me'  => $likedByMe,
                'comments'     => $book->comments->map(fn ($c) => [
                    'id'       => $c->id,
                    'body'     => $c->body,
                    'author'   => $c->author?->name,
                    'user_id'  => $c->user_id,
                    'time'     => $c->created_at->diffForHumans(),
                ]),
            ],
        ]);
    }
}
