<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Inertia\Inertia;

class CommentController extends Controller
{
    /**
     * Display paginated list of comments for dashboard
     */
    public function index()
    {
        $comments = Comment::with('author', 'commentable')
            ->latest()
            ->paginate(20);

        return Inertia::render('Dashboard/Comments/Index', [
            'comments' => $comments,
        ]);
    }

    /**
     * Approve a comment
     */
    public function approve(Comment $comment)
    {
        $comment->update(['is_approved' => true]);

        // ✅ Use a proper Inertia redirect
        return to_route('dashboard.comments.index')
            ->with('success', 'মন্তব্য অনুমোদিত হয়েছে।');
    }

    /**
     * Unapprove a comment
     */
    public function unapprove(Comment $comment)
    {
        $comment->update(['is_approved' => false]);

        return to_route('dashboard.comments.index')
            ->with('success', 'মন্তব্য বাতিল করা হয়েছে।');
    }

    /**
     * Delete a comment (soft delete)
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return to_route('dashboard.comments.index')
            ->with('success', 'মন্তব্য মুছে ফেলা হয়েছে।');
    }
}
