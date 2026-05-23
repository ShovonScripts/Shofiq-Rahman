<?php

namespace App\Jobs;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactReplyEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public int $timeout = 30;

    public function __construct(
        public int $contactMessageId,
        public string $replySubject,
        public string $replyBody
    ) {}

    public function handle(): void
    {
        $msg = ContactMessage::find($this->contactMessageId);

        if (! $msg) {
            return;
        }

        Mail::raw($this->replyBody, function ($m) use ($msg) {
            $m->to($msg->email)
                ->subject($this->replySubject)
                ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }
}
