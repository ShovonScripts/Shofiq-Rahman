<?php

// app/Mail/ContactMessageAutoReply.php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $msg) {}

    public function build()
    {
        return $this->subject('We received your message')
            ->view('emails.contact.autoreply');
    }
}
