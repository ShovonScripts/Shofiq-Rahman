<?php

namespace Tests\Feature;

use App\Jobs\SendContactMessageNotification;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_requires_name(): void
    {
        $response = $this->post('/contact', [
            'email' => 'test@example.com',
            'message' => 'Hello',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_contact_form_requires_valid_email(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'message' => 'Hello',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_contact_form_requires_message(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_contact_form_rejects_oversized_message(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_contact_form_stores_message_in_database(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_returns_success_message(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Hello there!',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();
    }

    public function test_contact_form_dispatches_notification_job(): void
    {
        Queue::fake();

        $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Hello!',
        ]);

        Queue::assertPushed(SendContactMessageNotification::class);
    }

    public function test_contact_form_subject_is_optional(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'No subject here.',
        ]);

        $response->assertSessionHasNoErrors();

        $msg = ContactMessage::latest()->first();
        $this->assertNull($msg->subject);
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Hello',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $payload)->assertSessionHasNoErrors();
        }

        $response = $this->post('/contact', $payload);
        $response->assertStatus(429);
    }
}
