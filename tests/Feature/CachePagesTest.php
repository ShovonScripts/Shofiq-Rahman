<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_with_cache_miss(): void
    {
        Cache::flush();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('banners')
            ->has('latest_posts')
            ->has('latest_books')
        );
    }

    public function test_homepage_renders_with_cache_hit(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
        );
    }

    public function test_blog_page_renders_with_cached_genres(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(200);

        $response = $this->get('/blog');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Blog')
            ->has('allGenres')
        );
    }

    public function test_categories_page_renders_with_cache(): void
    {
        $response = $this->get('/categories');
        $response->assertStatus(200);

        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories')
        );
    }

    public function test_books_page_renders_with_cached_filters(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);

        $response = $this->get('/books');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Books/Index')
            ->has('langs')
            ->has('years')
        );
    }

    public function test_about_page_renders_with_cache(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);

        $response = $this->get('/about');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('About')
        );
    }

    public function test_post_show_renders_even_with_cache_enabled(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->published()->create(['user_id' => $author->id]);

        $response = $this->get('/lekha/'.$post->slug);
        $response->assertStatus(200);

        $response = $this->get('/lekha/'.$post->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('PostShow')
            ->has('post')
        );
    }

    public function test_book_show_renders_even_with_cache_enabled(): void
    {
        $author = $this->authorUser();
        $book = Book::factory()->published()->create(['user_id' => $author->id]);

        $response = $this->get('/books/'.$book->slug);
        $response->assertStatus(200);

        $response = $this->get('/books/'.$book->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Books/Show')
            ->has('book')
        );
    }

    public function test_cached_pages_return_cache_control_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Cache-Control');
    }
}
