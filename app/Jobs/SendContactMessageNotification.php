<?php

namespace App\Jobs;

use App\Mail\ContactMessageAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public int $timeout = 30;

    public function __construct(
        public int $contactMessageId
    ) {}

    public function handle(): void
    {
        $msg = ContactMessage::find($this->contactMessageId);

        if (! $msg) {
            return;
        }

        Mail::to(config('mail.from.address'))
            ->send(new ContactMessageReceived($msg));

        Mail::to($msg->email)
            ->send(new ContactMessageAutoReply($msg));
    }
}
