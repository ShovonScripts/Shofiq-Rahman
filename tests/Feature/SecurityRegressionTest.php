<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_settings_reject_script_injection_and_accept_numeric_ids(): void
    {
        $this->actingAs($this->adminUser());
        $this->patch(route('dashboard.seo.update'), [
            'facebook_pixel_id' => "123');alert(document.domain);//",
            'facebook_app_id' => '<script>alert(1)</script>',
            'google_analytics_id' => "G-123');alert(1);//",
        ])->assertSessionHasErrors(['facebook_pixel_id', 'facebook_app_id', 'google_analytics_id']);

        $this->patch(route('dashboard.seo.update'), [
            'facebook_pixel_id' => '123456789', 'facebook_app_id' => '987654321',
            'google_analytics_id' => 'G-ABC123', 'google_tag_manager_id' => 'GTM-ABC123',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('123456789', Setting::get('facebook_pixel_id'));
    }

    public function test_every_book_link_mutation_requires_ownership_even_for_admins(): void
    {
        $owner = $this->authorUser();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $link = $book->purchaseLinks()->create(['label' => 'Seller', 'url' => 'https://example.com/original']);
        $endpoints = [
            ['post', route('dashboard.books.links.store', $book)],
            ['put', route('dashboard.books.links.update', [$book, $link])],
            ['delete', route('dashboard.books.links.destroy', [$book, $link])],
            ['patch', route('dashboard.books.links.primary', [$book, $link])],
            ['patch', route('dashboard.books.links.bump', [$book, $link, 'up'])],
        ];

        foreach ([$this->authorUser(), $this->adminUser()] as $other) {
            $this->actingAs($other);
            foreach ($endpoints as [$method, $url]) {
                $this->$method($url, ['label' => 'Changed', 'url' => 'https://example.com/changed'])
                    ->assertForbidden();
            }
        }
        $this->assertSame('https://example.com/original', $link->fresh()->url);
        $this->assertSame(1, $book->purchaseLinks()->count());

        $this->actingAs($owner)->put(route('dashboard.books.links.update', [$book, $link]), [
            'label' => 'Updated', 'url' => 'https://example.com/updated',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Updated', $link->fresh()->label);
    }

    public function test_a_link_from_another_book_cannot_be_mutated_through_an_owned_book(): void
    {
        $owner = $this->authorUser();
        $owned = Book::factory()->create(['user_id' => $owner->id]);
        $foreign = Book::factory()->create();
        $link = $foreign->purchaseLinks()->create(['label' => 'Seller', 'url' => 'https://example.com/book']);
        $this->actingAs($owner)->delete(route('dashboard.books.links.destroy', [$owned, $link]))->assertNotFound();
        $this->assertModelExists($link);
    }

    public function test_admin_and_author_dashboard_caches_do_not_leak_contact_messages(): void
    {
        $admin = $this->adminUser();
        $author = $this->authorUser();
        $other = $this->authorUser();
        $message = ContactMessage::factory()->create(['email' => 'private@example.com']);
        Post::factory()->create(['user_id' => $author->id]);
        Post::factory()->count(2)->create(['user_id' => $other->id]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.messages', 1)->where('stats.posts', 3)
            ->where('recent.messages.0.email', $message->email)
        );
        $this->actingAs($author)->get(route('dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.messages', null)->where('stats.users', null)
            ->where('stats.posts', 1)->has('recent.messages', 0)->has('recent.posts', 1)
            ->where('homepage', null)
        );
        $this->actingAs($other)->get(route('dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.posts', 2)->has('recent.messages', 0)
        );
    }

    public function test_unverified_accounts_cannot_access_verified_routes(): void
    {
        $user = User::factory()->author()->unverified()->create();
        $post = Post::factory()->published()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
        $this->postJson(route('likes.toggle'), ['type' => 'post', 'id' => $post->id])->assertForbidden();
        $this->postJson(route('comments.store'), ['type' => 'post', 'id' => $post->id, 'body' => 'Hello'])->assertForbidden();
        $this->assertDatabaseCount('likes', 0);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_inactive_accounts_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_changing_email_revokes_verification_without_resetting_omitted_preferences(): void
    {
        $user = $this->authorUser(['notify_new_comments' => true]);
        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name, 'email' => 'changed@example.com',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertTrue($user->fresh()->notify_new_comments);
        $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_authors_cannot_moderate_or_list_comments_on_other_authors_content(): void
    {
        $owner = $this->authorUser();
        $other = $this->authorUser();
        $post = Post::factory()->published()->create(['user_id' => $owner->id]);
        $comment = $post->comments()->create(['user_id' => $other->id, 'body' => 'Needs moderation', 'is_approved' => false]);

        $this->actingAs($other)->get(route('dashboard.comments.index'))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->has('comments.data', 0));
        $this->patch(route('dashboard.comments.approve', $comment))->assertForbidden();
        $this->patch(route('dashboard.comments.unapprove', $comment))->assertForbidden();
        $this->delete(route('dashboard.comments.destroy', $comment))->assertForbidden();
        $this->actingAs($owner)->patch(route('dashboard.comments.approve', $comment))->assertRedirect();
        $this->assertTrue($comment->fresh()->is_approved);
    }
}
