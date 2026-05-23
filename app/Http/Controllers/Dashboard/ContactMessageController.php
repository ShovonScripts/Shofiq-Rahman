<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\SendContactReplyEmail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class]);
    }

    public function index()
    {
        $messages = ContactMessage::orderByDesc('created_at')
            ->select('id', 'name', 'email', 'phone', 'subject', 'message', 'status', 'created_at', 'replied_at')
            ->paginate(15);

        return Inertia::render('Dashboard/Contacts/Index', compact('messages'));
    }

    public function show(ContactMessage $message)
    {
        // Fix: Handle both 'new' and 'unread' statuses
        if ($message->status === 'new' || $message->status === 'unread') {
            $message->update(['status' => 'read']);
            $message->refresh(); // Refresh the model to get updated status
        }

        return Inertia::render('Dashboard/Contacts/Show', [
            'message' => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'phone' => $message->phone ?? '', // Handle null phone
                'subject' => $message->subject ?? 'No Subject',
                'message' => $message->message,
                'status' => $message->status,
                'ip_address' => $message->ip_address ?? 'Unknown', // Handle null ip_address
                'replied_at' => $message->replied_at?->toDateTimeString(),
                'created_at' => $message->created_at?->toDateTimeString(),
                'reply_subject' => $message->reply_subject ?? '',
                'reply_body' => $message->reply_body ?? '',
            ],
        ]);
    }

    public function update(Request $request, ContactMessage $message)
    {
        $request->validate([
            'status' => 'required|in:new,unread,read,replied',
        ]);

        $message->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Message status updated successfully.');
    }

    public function reply(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'reply_subject' => ['required', 'string', 'max:180'],
            'reply_body' => ['required', 'string', 'max:5000'],
        ]);

        $message->update([
            'status' => 'replied',
            'replied_at' => now(),
            'reply_subject' => $data['reply_subject'],
            'reply_body' => $data['reply_body'],
        ]);

        SendContactReplyEmail::dispatch(
            $message->id,
            $data['reply_subject'],
            $data['reply_body']
        );

        return back()->with('success', 'Reply queued and will be sent shortly!');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('dashboard.contacts.index')->with('success', 'Message deleted successfully.');
    }
}
