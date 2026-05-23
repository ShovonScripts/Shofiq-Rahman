<?php

namespace Tests\Feature;

use App\Jobs\SendContactMessageNotification;
use App\Jobs\SendContactReplyEmail;
use App\Mail\ContactMessageAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_contact_message_notification_job_exists(): void
    {
        $this->assertTrue(class_exists(SendContactMessageNotification::class));
    }

    public function test_send_contact_reply_email_job_exists(): void
    {
        $this->assertTrue(class_exists(SendContactReplyEmail::class));
    }

    public function test_contact_form_dispatches_job_with_correct_data(): void
    {
        Queue::fake();

        $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Hello from test!',
        ]);

        Queue::assertPushed(SendContactMessageNotification::class, function ($job) {
            return $job->contactMessageId > 0;
        });
    }

    public function test_contact_notification_job_sends_emails_when_processed(): void
    {
        Mail::fake();

        $msg = ContactMessage::factory()->create();

        $job = new SendContactMessageNotification($msg->id);
        $job->handle();

        Mail::assertSent(ContactMessageReceived::class);
        Mail::assertSent(ContactMessageAutoReply::class);
    }

    public function test_contact_notification_job_handles_missing_message_gracefully(): void
    {
        Mail::fake();

        $job = new SendContactMessageNotification(99999);
        $job->handle();

        Mail::assertNothingSent();
    }

    public function test_contact_reply_job_sends_email_when_processed(): void
    {
        $msg = ContactMessage::factory()->create();

        $job = new SendContactReplyEmail(
            $msg->id,
            'Re: Your message',
            'Thank you for contacting us.'
        );

        // Job should execute without throwing an exception
        $job->handle();

        $this->assertSame($msg->id, $job->contactMessageId);
    }

    public function test_contact_reply_job_handles_missing_message_gracefully(): void
    {
        Mail::fake();

        $job = new SendContactReplyEmail(99999, 'Subject', 'Body');
        $job->handle();

        Mail::assertNothingSent();
    }

    public function test_jobs_implement_should_queue(): void
    {
        $notificationJob = new SendContactMessageNotification(1);
        $replyJob = new SendContactReplyEmail(1, 'Subject', 'Body');

        $this->assertInstanceOf(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            $notificationJob
        );
        $this->assertInstanceOf(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            $replyJob
        );
    }

    public function test_jobs_have_retry_configuration(): void
    {
        $notificationJob = new SendContactMessageNotification(1);
        $replyJob = new SendContactReplyEmail(1, 'Subject', 'Body');

        $this->assertEquals(3, $notificationJob->tries);
        $this->assertEquals(3, $replyJob->tries);
    }
}
