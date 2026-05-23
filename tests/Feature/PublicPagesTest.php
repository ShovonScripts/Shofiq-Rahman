<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('banners')
            ->has('latest_posts')
            ->has('latest_books')
            ->has('author')
        );
    }

    public function test_blog_page_loads_successfully(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Blog')
            ->has('posts')
            ->has('allGenres')
        );
    }

    public function test_blog_page_shows_published_posts(): void
    {
        $author = User::factory()->author()->create();
        Post::factory()->published()->count(3)->create(['user_id' => $author->id]);
        Post::factory()->draft()->count(2)->create(['user_id' => $author->id]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Blog')
            ->has('posts.data', 3)
        );
    }

    public function test_books_page_loads_successfully(): void
    {
        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Books/Index')
            ->has('books')
            ->has('allGenres')
            ->has('allCategories')
        );
    }

    public function test_books_page_shows_published_books(): void
    {
        $author = User::factory()->author()->create();
        Book::factory()->published()->count(2)->create(['user_id' => $author->id]);
        Book::factory()->draft()->count(2)->create(['user_id' => $author->id]);

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Books/Index')
            ->has('books.data', 2)
        );
    }

    public function test_about_page_loads(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('About')
        );
    }

    public function test_categories_index_loads(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories')
        );
    }

    public function test_category_show_loads_with_valid_slug(): void
    {
        $category = Category::factory()->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('CategoryShow')
            ->has('category')
            ->has('posts')
        );
    }

    public function test_genre_show_loads_with_valid_slug(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get('/genre/'.$genre->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('GenreShow')
            ->has('genre')
            ->has('posts')
        );
    }

    public function test_post_show_loads_with_valid_slug(): void
    {
        $author = User::factory()->author()->create();
        $post = Post::factory()->published()->create(['user_id' => $author->id]);

        $response = $this->get('/lekha/'.$post->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('PostShow')
            ->has('post')
        );
    }

    public function test_book_show_loads_with_valid_slug(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->published()->create(['user_id' => $author->id]);

        $response = $this->get('/books/'.$book->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Books/Show')
            ->has('book')
        );
    }

    public function test_missing_post_slug_returns_404(): void
    {
        $response = $this->get('/lekha/non-existent-slug-xyz');

        $response->assertStatus(404);
    }

    public function test_missing_book_slug_returns_404(): void
    {
        $response = $this->get('/books/non-existent-slug-xyz');

        $response->assertStatus(404);
    }

    public function test_draft_post_returns_404_for_public(): void
    {
        $author = User::factory()->author()->create();
        $post = Post::factory()->draft()->create(['user_id' => $author->id]);

        $response = $this->get('/lekha/'.$post->slug);

        $response->assertStatus(404);
    }

    public function test_draft_book_returns_404_for_public(): void
    {
        $author = User::factory()->author()->create();
        $book = Book::factory()->draft()->create(['user_id' => $author->id]);

        $response = $this->get('/books/'.$book->slug);

        $response->assertStatus(404);
    }

    public function test_contact_page_loads(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Contact')
        );
    }

    public function test_rss_feed_returns_xml(): void
    {
        $response = $this->get('/feed');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type');
    }

    public function test_sitemap_returns_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type');
    }

    public function test_robots_returns_text(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
    }
}
