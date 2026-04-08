// resources/views/emails/contact/autoreply.blade.php
<p>Hi {{ $msg->name }},</p>
<p>Thanks for reaching out! We’ve received your message and will get back to you soon.</p>
@if($msg->subject)<p><strong>Subject:</strong> {{ $msg->subject }}</p>@endif
<p><strong>Your message:</strong></p>
<p style="white-space:pre-wrap">{{ $msg->message }}</p>
<p>— {{ config('app.name') }}</p>
