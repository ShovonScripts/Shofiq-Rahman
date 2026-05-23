<?php

namespace App\Http\Controllers;

use App\Jobs\SendContactMessageNotification;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $msg = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
        ]);

        SendContactMessageNotification::dispatch($msg->id);

        return back()->with('success', 'ধন্যবাদ! আপনার বার্তা আমরা পেয়েছি।');
    }
}
