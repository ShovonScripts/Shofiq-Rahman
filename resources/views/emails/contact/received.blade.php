// resources/views/emails/contact/received.blade.php
<p>New contact message:</p>
<ul>
  <li><strong>Name:</strong> {{ $msg->name }}</li>
  <li><strong>Email:</strong> {{ $msg->email }}</li>
  @if($msg->subject)<li><strong>Subject:</strong> {{ $msg->subject }}</li>@endif
</ul>
<p><strong>Message:</strong></p>
<p style="white-space:pre-wrap">{{ $msg->message }}</p>
<p>— {{ config('app.name') }}</p>
