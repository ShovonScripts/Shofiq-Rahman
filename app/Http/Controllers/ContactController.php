<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Mail\ContactMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:100'],
            'email'   => ['required','email','max:150'],
            'subject' => ['nullable','string','max:150'],
            'message' => ['required','string','max:5000'],
        ]);

        // Eloquent create => safe bindings (কোনো র’ SQL না)
        $msg = ContactMessage::create([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status'  => 'new',
        ]);

        // মেইল পাঠাই; লোকালে log mailer হলে শুধু লগ হবে
        try {
            // চাইলে লোকালে মেইল স্কিপ করতে পারেন:
            // if (app()->environment('local')) { /* skip */ } else { ... }

            Mail::to(config('mail.from.address'))
                ->send(new ContactMessageReceived($msg));
        } catch (\Throwable $e) {
            report($e);
            // ইউজারের কাছে ফেইলিওর না দেখিয়েও গ্রেসফুলি জানাতে পারেন:
            // return back()->with('success', 'Message received. (Email delivery deferred)');
        }

        return back()->with('success', 'ধন্যবাদ! আপনার বার্তা আমরা পেয়েছি।');
    }
}
