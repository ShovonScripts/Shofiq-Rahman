<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Post;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublishingRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_edit_props_and_an_unchanged_update_preserve_taxonomies(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->draft()->create(['user_id' => $author->id]);
        $category = Category::create(['name' => 'Essays', 'slug' => 'essays']);
        $genre = Genre::create(['name' => 'Fiction', 'slug' => 'fiction']);
        $post->categories()->attach($category);
        $post->genres()->attach($genre);

        $this->actingAs($author)->get(route('dashboard.posts.edit', $post))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Posts/Edit')
            ->where('post.selected_category_ids', [$category->id])
            ->where('post.selected_genre_ids', [$genre->id])
            );

        $this->put(route('dashboard.posts.update', $post), [
            'title' => $post->title, 'body' => $post->body, 'status' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame([$category->id], $post->categories()->pluck('categories.id')->all());
        $this->assertSame([$genre->id], $post->genres()->pluck('genres.id')->all());
    }

    public function test_explicit_empty_taxonomies_clear_selections_and_post_uploads_work(): void
    {
        Storage::fake('public');
        $author = $this->authorUser();
        $post = Post::factory()->draft()->create(['user_id' => $author->id]);
        $post->categories()->attach(Category::create(['name' => 'Essays', 'slug' => 'essays']));
        $post->genres()->attach(Genre::create(['name' => 'Fiction', 'slug' => 'fiction']));

        $this->actingAs($author)->post(route('dashboard.posts.update', $post), [
            '_method' => 'put', 'title' => $post->title, 'body' => $post->body,
            'status' => 'draft', 'category_ids' => null, 'genre_ids' => null,
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(0, $post->categories()->count());
        $this->assertSame(0, $post->genres()->count());
        Storage::disk('public')->assertExists($post->fresh()->cover);
    }

    public function test_scheduled_post_edits_preserve_the_time_of_day(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->create([
            'user_id' => $author->id, 'status' => 'scheduled',
            'published_at' => '2026-09-30 15:30:00',
        ]);
        $this->actingAs($author)->get(route('dashboard.posts.edit', $post))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('post.published_at', '2026-09-30T15:30'));
        $this->put(route('dashboard.posts.update', $post), [
            'title' => $post->title, 'body' => $post->body, 'status' => 'scheduled',
            'published_at' => '2026-09-30T15:30',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('2026-09-30 15:30:00', $post->fresh()->published_at->toDateTimeString());
    }

    public function test_scheduled_posts_require_a_publication_time(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->draft()->create(['user_id' => $author->id]);
        $this->actingAs($author)->put(route('dashboard.posts.update', $post), [
            'title' => $post->title, 'body' => $post->body, 'status' => 'scheduled',
        ])->assertSessionHasErrors('published_at');
        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_scheduler_publishes_only_due_scheduled_posts_and_invalidates_caches(): void
    {
        $this->freezeTime();
        $due = Post::factory()->create(['status' => 'scheduled', 'published_at' => now()->subMinute()]);
        $future = Post::factory()->create(['status' => 'scheduled', 'published_at' => now()->addMinute()]);
        $draft = Post::factory()->draft()->create();
        $undated = Post::factory()->create(['status' => 'scheduled', 'published_at' => null]);
        $keys = [CacheKeys::latestPosts(), CacheKeys::categoriesWithPostCount(), 'rss.items', 'sitemap.xml'];
        foreach ($keys as $key) {
            Cache::put($key, 'stale');
        }

        $this->artisan('posts:publish-scheduled')->expectsOutput('Published 1 scheduled post(s).')->assertSuccessful();
        $this->assertSame('published', $due->fresh()->status);
        $this->assertSame('scheduled', $future->fresh()->status);
        $this->assertSame('draft', $draft->fresh()->status);
        $this->assertSame('scheduled', $undated->fresh()->status);
        $this->assertTrue(Post::published()->whereKey($due->id)->exists());
        foreach ($keys as $key) {
            $this->assertFalse(Cache::has($key));
        }
        $this->artisan('posts:publish-scheduled')->expectsOutput('Published 0 scheduled post(s).')->assertSuccessful();
    }

    public function test_public_post_and_book_pages_hide_pending_and_deleted_comments(): void
    {
        $reader = $this->readerUser();
        foreach ([Post::factory()->published()->create(), Book::factory()->published()->create()] as $content) {
            $approved = $content->comments()->create(['user_id' => $reader->id, 'body' => 'Approved', 'is_approved' => true]);
            $content->comments()->create(['user_id' => $reader->id, 'body' => 'Pending private text', 'is_approved' => false]);
            $content->comments()->create(['user_id' => $reader->id, 'body' => 'Deleted', 'is_approved' => true])->delete();
            $isPost = $content instanceof Post;
            $prop = $isPost ? 'post' : 'book';

            $this->get(route($isPost ? 'posts.show' : 'books.show', $content->slug))
                ->assertOk()->assertInertia(fn (Assert $page) => $page
                ->has($prop.'.comments', 1)
                ->where($prop.'.comments.0.id', $approved->id)
                );
        }
    }

    public function test_category_counts_include_only_published_posts(): void
    {
        $category = Category::create(['name' => 'Essays', 'slug' => 'essays']);
        $category->posts()->attach(Post::factory()->published()->create());
        $category->posts()->attach(Post::factory()->draft()->create());
        $category->posts()->attach(Post::factory()->create(['status' => 'scheduled', 'published_at' => now()->addDay()]));
        $this->get(route('categories.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('categories.0.post_count', 1)
        );
    }

    public function test_sitemap_uses_the_live_book_route_and_excludes_drafts(): void
    {
        $published = Book::factory()->published()->create(['slug' => 'published-book']);
        $draft = Book::factory()->draft()->create(['slug' => 'draft-book']);
        $response = $this->get(route('sitemap'))->assertOk();
        $response->assertSee('/books/'.$published->slug, false)
            ->assertDontSee('/boi/', false)->assertDontSee($draft->slug, false);
    }

    public function test_like_endpoint_returns_json_and_rejects_draft_content(): void
    {
        $this->actingAs($this->readerUser());
        foreach (['post' => Post::factory(), 'book' => Book::factory()] as $type => $factory) {
            $content = $factory->published()->create();
            $draft = $factory->draft()->create();
            $this->postJson(route('likes.toggle'), ['type' => $type, 'id' => $content->id])
                ->assertOk()->assertExactJson(['liked' => true, 'count' => 1]);
            $this->postJson(route('likes.toggle'), ['type' => $type, 'id' => $content->id])
                ->assertOk()->assertExactJson(['liked' => false, 'count' => 0]);
            $this->postJson(route('likes.toggle'), ['type' => $type, 'id' => $draft->id])->assertNotFound();
        }
    }
}
