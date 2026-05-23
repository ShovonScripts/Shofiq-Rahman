<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        // Middleware শুধুমাত্র Controller routes এর জন্য
        $this->middleware('auth')->only(['store', 'destroy', 'approve', 'unapprove']);
        $this->middleware('verified')->only('store');
        $this->middleware('throttle:10,1')->only('store');
    }

    /**
     * Store a new comment
     */
    public function store(Request $request)
    {
        $this->authorize('create', Comment::class);

        $data = $request->validate([
            'type' => 'required|in:post,book',
            'id' => 'required|integer',
            'body' => 'required|string|min:2|max:3000',
            'website' => 'nullable|size:0', // honeypot
        ]);

        if ($request->filled('website')) {
            abort(403, 'Spam detected');
        }

        $model = $data['type'] === 'post'
            ? Post::published()->findOrFail($data['id'])
            : Book::published()->findOrFail($data['id']);

        $comment = $model->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim($data['body']),
            'is_approved' => false, // moderation-ready
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'author' => $request->user()->name,
                    'user_id' => $request->user()->id,
                    'time' => $comment->created_at->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'মন্তব্য যুক্ত হয়েছে। (মডারেশন অপেক্ষা)');
    }

    /**
     * Delete a comment (soft delete)
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete(); // soft delete

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'মন্তব্য মুছে ফেলা হয়েছে।');
    }

    /**
     * Approve a comment (dashboard)
     */
    public function approve(Comment $comment)
    {
        $this->authorize('update', $comment);
        $comment->update(['is_approved' => true]);

        return back()->with('success', 'মন্তব্য অনুমোদিত হয়েছে।');
    }

    /**
     * Unapprove a comment (dashboard)
     */
    public function unapprove(Comment $comment)
    {
        $this->authorize('update', $comment);
        $comment->update(['is_approved' => false]);

        return back()->with('success', 'মন্তব্য বাতিল করা হয়েছে।');
    }

    /**
     * Dashboard: list all comments
     */
    public function index()
    {
        $this->authorize('viewAny', Comment::class);

        $comments = Comment::with('author', 'commentable')
            ->latest()
            ->paginate(20);

        return view('dashboard.comments.index', compact('comments'));
    }
}
