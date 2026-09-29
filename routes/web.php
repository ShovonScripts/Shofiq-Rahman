<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookLinkController as PublicBookLinkController;
// Public controllers
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\AuthorAchievementController;
use App\Http\Controllers\Dashboard\AuthorEventController;
use App\Http\Controllers\Dashboard\AuthorProfileController;
use App\Http\Controllers\Dashboard\BookController as DashboardBookController;
use App\Http\Controllers\Dashboard\BookLinkController as DashboardBookLinkController;
use App\Http\Controllers\Dashboard\CategoryController as DashboardCategoryController;
use App\Http\Controllers\Dashboard\CommentController as DashboardCommentController;
use App\Http\Controllers\Dashboard\ContactMessageController as DashboardContactController; // Public About page
// Dashboard controllers
use App\Http\Controllers\Dashboard\DashboardController;               // main dashboard
use App\Http\Controllers\Dashboard\GenreController as DashboardGenreController;
use App\Http\Controllers\Dashboard\HomepageController;
use App\Http\Controllers\Dashboard\PostController as DashboardPostController;
use App\Http\Controllers\Dashboard\SeoController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\UserController as AdminUserController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
// Dashboard About controllers
use App\Models\Book;
use App\Models\BookPurchaseLink;
use App\Models\Category;
use App\Models\Post;
// Models used in sitemap builder
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
// ✅ Explicit model binding target for public link redirect
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Global route parameter patterns
|--------------------------------------------------------------------------
*/
Route::pattern('slug', '[^/]+');
Route::pattern('user', '[0-9]+');
Route::pattern('post', '[0-9]+');
Route::pattern('book', '[0-9]+');
Route::pattern('link', '[0-9]+');   // purchase_link id
Route::pattern('banner', '[0-9]+');
Route::pattern('comment', '[0-9]+');
Route::pattern('message', '[0-9]+');

// About system ids
Route::pattern('achievement', '[0-9]+');
Route::pattern('event', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Explicit model bindings
|--------------------------------------------------------------------------
|
| IMPORTANT: Controller method go(Request $r, BookPurchaseLink $link)
|            so bind {link} to BookPurchaseLink::class
*/
Route::model('link', BookPurchaseLink::class);

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

// Static pages
Route::get('/about', [AboutController::class, 'show'])->name('about');

Route::get('/contact', fn () => Inertia::render('Contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

// Feeds
Route::get('/feed', [FeedController::class, 'index'])->name('rss');
Route::get('/rss.xsl', function () {
    return response()->view('rss-xsl')
        ->header('Content-Type', 'application/xslt+xml; charset=UTF-8');
})->name('rss.xsl');

// SEO files
Route::get('/robots.txt', function () {
    return response()->view('robots')
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots')->middleware('cache.headers:public;max_age=3600;etag');

Route::get('/sitemap.xml', function () {
    $site = rtrim(config('app.url'), '/');

    $xml = Cache::remember('sitemap.xml', now()->addMinutes(60), function () use ($site) {
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->select('slug', 'updated_at', 'published_at')
            ->limit(5000)
            ->get();

        $cats = Category::select('slug', 'updated_at')->get();

        $books = Book::published()
            ->orderByDesc('created_at')
            ->select('slug', 'updated_at')
            ->limit(500)
            ->get();

        $genres = \App\Models\Genre::select('slug', 'updated_at')->get();

        $items = [];
        $add = function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.7') use (&$items) {
            $locEsc = e($loc);
            $last = $lastmod ? '<lastmod>'.e($lastmod).'</lastmod>' : '';
            $items[] = "<url><loc>{$locEsc}</loc>{$last}<changefreq>{$changefreq}</changefreq><priority>{$priority}</priority></url>";
        };

        $nowIso = now()->toAtomString();
        $add("{$site}/", $nowIso, 'daily', '1.0');
        $add("{$site}/about", $nowIso, 'monthly', '0.3');
        $add("{$site}/contact", $nowIso, 'monthly', '0.3');
        $add("{$site}/blog", $nowIso, 'daily', '0.8');
        $add("{$site}/books", $nowIso, 'weekly', '0.7');
        $add("{$site}/categories", $nowIso, 'weekly', '0.6');

        foreach ($cats as $c) {
            $add("{$site}/category/{$c->slug}", optional($c->updated_at)->toAtomString(), 'weekly', '0.6');
        }

        foreach ($posts as $p) {
            $last = optional($p->updated_at ?? $p->published_at)->toAtomString();
            $add("{$site}/lekha/{$p->slug}", $last, 'weekly', '0.8');
        }

        foreach ($books as $b) {
            $last = optional($b->updated_at)->toAtomString();
            $add($site.route('books.show', ['slug' => $b->slug], false), $last, 'monthly', '0.6');
        }

        foreach ($genres as $g) {
            $add("{$site}/genre/{$g->slug}", optional($g->updated_at)->toAtomString(), 'monthly', '0.5');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', $items)
            .'</urlset>';
    });

    return response($xml, 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('sitemap')->middleware('cache.headers:public;max_age=3600;etag');

/*
|--------------------------------------------------------------------------
| Public Landing + Blog
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog routes
Route::controller(PostController::class)->group(function () {
    Route::get('/blog', 'index')->name('blog');
    Route::get('/search', 'search')->name('search');
    Route::get('/lekha/{slug}', 'show')->name('posts.show');
    Route::get('/categories', 'categoriesIndex')->name('categories.index');
    Route::get('/category/{slug}', 'byCategory')->name('category.show');
    Route::get('/genre/{slug}', 'byGenre')->name('genre.show');
});

/*
|--------------------------------------------------------------------------
| Public Book pages
|--------------------------------------------------------------------------
*/
Route::controller(BookController::class)->group(function () {
    Route::get('/books', 'index')->name('books.index');
    Route::get('/books/{slug}', 'show')->name('books.show');
});

/*
|--------------------------------------------------------------------------
| Public redirect for purchase links
| Explicit binding → BookPurchaseLink $link, logs click → redirects externally
|--------------------------------------------------------------------------
*/
Route::get('/go/book-link/{link}', [PublicBookLinkController::class, 'go'])
    ->whereNumber('link')
    ->middleware('throttle:60,1')
    ->name('books.link.go');

/*
|--------------------------------------------------------------------------
| Auth-only routes (outside /dashboard prefix)
|--------------------------------------------------------------------------
*/
// Main dashboard index (Ziggy-friendly) → author/admin only
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureAdmin::class.':author'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes (/dashboard/*)
|--------------------------------------------------------------------------
*/
// Entire dashboard area → author/admin only
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureAdmin::class.':author'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        // Admin-only routes
        Route::middleware([\App\Http\Middleware\EnsureAdmin::class.':admin'])->group(function () {

            // Users management
            Route::resource('users', AdminUserController::class)->only(['index', 'store', 'destroy']);
            Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.updateRole')->whereNumber('user');
            Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggleActive')->whereNumber('user');
            Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.resetPassword')->whereNumber('user');

            // Homepage management
            Route::prefix('homepage')->group(function () {
                Route::get('/', [HomepageController::class, 'edit'])->name('homepage.edit');

                Route::post('/banners', [HomepageController::class, 'storeBanner'])->name('homepage.banners.store');
                Route::put('/banners/{banner}', [HomepageController::class, 'updateBanner'])->name('homepage.banners.update')->whereNumber('banner');
                Route::delete('/banners/{banner}', [HomepageController::class, 'destroyBanner'])->name('homepage.banners.destroy')->whereNumber('banner');
                Route::post('/banners/sort', [HomepageController::class, 'sortBanners'])->name('homepage.banners.sort');
                Route::patch('/banners/{banner}/toggle', [HomepageController::class, 'toggleBanner'])->name('homepage.banners.toggle')->whereNumber('banner');

                // Author card update — PUT
                Route::put('/author', [HomepageController::class, 'updateAuthor'])->name('homepage.author.update');
                Route::delete('/author/photo', [HomepageController::class, 'deleteAuthorPhoto'])->name('homepage.author.photo.delete');
            });

            // Contact messages
            Route::resource('contacts', DashboardContactController::class)
                ->only(['index', 'show', 'destroy'])
                ->parameters(['contacts' => 'message']); // use {message} for model binding

            Route::post('/contacts/{message}/reply', [DashboardContactController::class, 'reply'])
                ->name('contacts.reply')->whereNumber('message');

            Route::patch('/contacts/{message}', [DashboardContactController::class, 'update'])
                ->name('contacts.update')->whereNumber('message'); // status update route
        });

        // Posts management (for authors and admins)
        Route::resource('posts', DashboardPostController::class)->except(['show']);
        Route::patch('/posts/{post}/toggle', [DashboardPostController::class, 'toggle'])
            ->name('posts.toggle')
            ->whereNumber('post');

        // Books management
        Route::resource('books', DashboardBookController::class)->except(['show']);

        // Book purchase links (CRUD under a book)
        Route::prefix('books/{book}')->group(function () {
            Route::post('/links', [DashboardBookLinkController::class, 'store'])->name('books.links.store')->whereNumber('book');
            Route::put('/links/{link}', [DashboardBookLinkController::class, 'update'])->name('books.links.update')->whereNumber('book')->whereNumber('link');
            Route::delete('/links/{link}', [DashboardBookLinkController::class, 'destroy'])->name('books.links.destroy')->whereNumber('book')->whereNumber('link');
            Route::patch('/links/{link}/primary', [DashboardBookLinkController::class, 'makePrimary'])->name('books.links.primary')->whereNumber('book')->whereNumber('link');
            Route::patch('/links/{link}/sort/{dir}', [DashboardBookLinkController::class, 'bump'])
                ->whereNumber('book')->whereNumber('link')
                ->whereIn('dir', ['up', 'down'])
                ->name('books.links.bump');
        });

        // Genres management
        Route::resource('genres', DashboardGenreController::class)->except(['show']);

        // Categories management
        Route::resource('categories', DashboardCategoryController::class)->except(['show']);

        // Comments moderation (explicit approve/unapprove)
        Route::get('/comments', [DashboardCommentController::class, 'index'])->name('comments.index');
        Route::patch('/comments/{comment}/approve', [DashboardCommentController::class, 'approve'])->name('comments.approve')->whereNumber('comment');
        Route::patch('/comments/{comment}/unapprove', [DashboardCommentController::class, 'unapprove'])->name('comments.unapprove')->whereNumber('comment');
        Route::delete('/comments/{comment}', [DashboardCommentController::class, 'destroy'])->name('comments.destroy')->whereNumber('comment');

        // Settings
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email', [SettingController::class, 'sendTestEmail'])->name('settings.test');

        // SEO Manager
        Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
        Route::patch('/seo', [SeoController::class, 'update'])->name('seo.update');
        Route::get('/seo/analyze', [SeoController::class, 'analyze'])->name('seo.analyze');

        /*
        |--------------------------------------------------------------------------
        | About Management (Authors & Admins)
        |--------------------------------------------------------------------------
        */
        Route::get('/about', [AuthorProfileController::class, 'edit'])->name('about.edit');
        Route::put('/about', [AuthorProfileController::class, 'update'])->name('about.update');

        // Achievements CRUD
        Route::prefix('about/achievements')->name('about.achievements.')->group(function () {
            Route::get('/', [AuthorAchievementController::class, 'index'])->name('index');
            Route::get('/create', [AuthorAchievementController::class, 'create'])->name('create');
            Route::post('/', [AuthorAchievementController::class, 'store'])->name('store');
            Route::get('/{achievement}/edit', [AuthorAchievementController::class, 'edit'])->name('edit')->whereNumber('achievement');
            Route::put('/{achievement}', [AuthorAchievementController::class, 'update'])->name('update')->whereNumber('achievement');
            Route::delete('/{achievement}', [AuthorAchievementController::class, 'destroy'])->name('destroy')->whereNumber('achievement');
        });

        // Events CRUD
        Route::prefix('about/events')->name('about.events.')->group(function () {
            Route::get('/', [AuthorEventController::class, 'index'])->name('index');
            Route::get('/create', [AuthorEventController::class, 'create'])->name('create');
            Route::post('/', [AuthorEventController::class, 'store'])->name('store');
            Route::get('/{event}/edit', [AuthorEventController::class, 'edit'])->name('edit')->whereNumber('event');
            Route::put('/{event}', [AuthorEventController::class, 'update'])->name('update')->whereNumber('event');
            Route::delete('/{event}', [AuthorEventController::class, 'destroy'])->name('destroy')->whereNumber('event');
        });

    });

/*
|--------------------------------------------------------------------------
| Comments & Likes (public auth actions)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'throttle:10,1'])->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/likes/toggle', [LikeController::class, 'toggle'])->name('likes.toggle');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy')->whereNumber('comment');
});

/*
|--------------------------------------------------------------------------
| Local-only testing routes
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::prefix('_dev')->name('_dev.')->group(function () {
        Route::get('/create', [DashboardPostController::class, 'create'])->name('create');

        Route::post('/posts', [DashboardPostController::class, 'store'])
            ->name('posts.store')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        Route::get('/books/create', [DashboardBookController::class, 'create'])->name('books.create');

        Route::post('/books', [DashboardBookController::class, 'store'])
            ->name('books.store')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    });
}

require __DIR__.'/auth.php';
