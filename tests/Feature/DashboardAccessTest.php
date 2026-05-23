<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_reader_cannot_access_dashboard(): void
    {
        $reader = $this->readerUser();

        $response = $this->actingAs($reader)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_inactive_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->author()->inactive()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_author_can_access_dashboard(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->get('/dashboard');

        $response->assertOk();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
    }

    public function test_reader_cannot_access_dashboard_posts(): void
    {
        $reader = $this->readerUser();

        $response = $this->actingAs($reader)->get('/dashboard/posts');

        $response->assertForbidden();
    }

    public function test_author_can_access_dashboard_posts(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->get('/dashboard/posts');

        $response->assertOk();
    }

    public function test_author_can_access_dashboard_books(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->get('/dashboard/books');

        $response->assertOk();
    }

    public function test_author_can_access_admin_users_route(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->get('/dashboard/users');

        $response->assertOk();
    }

    public function test_admin_can_access_users_management(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/users');

        $response->assertOk();
    }

    public function test_admin_can_access_settings(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/settings');

        $response->assertOk();
    }

    public function test_author_can_access_settings(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->get('/dashboard/settings');

        $response->assertOk();
    }

    public function test_admin_can_access_contact_messages(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/contacts');

        $response->assertOk();
    }

    public function test_author_can_access_contact_messages(): void
    {
        $author = $this->authorUser();

        $response = $this->actingAs($author)->get('/dashboard/contacts');

        $response->assertOk();
    }
}
