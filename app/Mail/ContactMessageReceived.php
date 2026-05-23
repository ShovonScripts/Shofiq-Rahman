<?php

// app/Mail/ContactMessageReceived.php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $msg) {}

    public function build()
    {
        return $this->subject('New contact message: '.$this->msg->subject)
            ->view('emails.contact.received');
    }
}
