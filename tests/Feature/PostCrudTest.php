<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_create_post(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->post('/dashboard/posts', [
            'title' => 'My First Post',
            'body' => 'This is the content of my first post.',
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post',
            'status' => 'draft',
        ]);
    }

    public function test_author_can_see_own_posts(): void
    {
        $author = $this->authorUser();
        Post::factory()->count(3)->create(['user_id' => $author->id]);

        $otherAuthor = $this->authorUser();
        Post::factory()->count(2)->create(['user_id' => $otherAuthor->id]);

        $response = $this->actingAs($author)->get('/dashboard/posts');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Posts/Index')
            ->has('posts')
        );
    }

    public function test_author_can_edit_own_post(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->create(['user_id' => $author->id, 'status' => 'draft']);

        $response = $this->actingAs($author)->patch('/dashboard/posts/'.$post->id, [
            'title' => 'Updated Title',
            'body' => 'Updated body content.',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'status' => 'published',
        ]);
    }

    public function test_author_cannot_edit_other_authors_post(): void
    {
        $author1 = $this->authorUser();
        $author2 = $this->authorUser();
        $post = Post::factory()->create(['user_id' => $author1->id, 'status' => 'draft']);

        $response = $this->actingAs($author2)->patch('/dashboard/posts/'.$post->id, [
            'title' => 'Hacked Title',
            'body' => 'Hacked content.',
            'status' => 'draft',
        ]);

        $response->assertForbidden();
    }

    public function test_author_cannot_delete_other_authors_post(): void
    {
        $author1 = $this->authorUser();
        $author2 = $this->authorUser();
        $post = Post::factory()->create(['user_id' => $author1->id]);

        $response = $this->actingAs($author2)->delete('/dashboard/posts/'.$post->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_author_can_delete_own_post(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($author)->delete('/dashboard/posts/'.$post->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_author_can_toggle_post_publish_status(): void
    {
        $author = $this->authorUser();
        $post = Post::factory()->create(['user_id' => $author->id, 'status' => 'draft']);

        $response = $this->actingAs($author)->patch('/dashboard/posts/'.$post->id.'/toggle');

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_edit_other_authors_post_by_policy(): void
    {
        $author = $this->authorUser();
        $admin = $this->adminUser();
        $post = Post::factory()->create(['user_id' => $author->id, 'status' => 'draft']);

        $response = $this->actingAs($admin)->patch('/dashboard/posts/'.$post->id, [
            'title' => 'Admin Updated Title',
            'body' => 'Admin updated content.',
            'status' => 'published',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->post('/dashboard/posts', [
            'title' => 'Unauthorized Post',
            'body' => 'This should not work.',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_reader_cannot_create_post(): void
    {
        $reader = $this->readerUser();

        $response = $this->actingAs($reader)->post('/dashboard/posts', [
            'title' => 'Reader Post',
            'body' => 'Readers cannot create posts.',
            'status' => 'draft',
        ]);

        $response->assertForbidden();
    }
}
