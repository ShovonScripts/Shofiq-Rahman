<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Comment;
use App\Models\Post;
use Inertia\Inertia;

class CommentController extends Controller
{
    /**
     * Display paginated list of comments for dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $comments = Comment::with('author', 'commentable')
            ->when(! $user->isAdmin(), fn ($q) => $q->whereHasMorph(
                'commentable', [Post::class, Book::class], fn ($parent) => $parent->where('user_id', $user->id)
            ))
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
        $this->authorize('moderate', $comment);

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
        $this->authorize('moderate', $comment);

        $comment->update(['is_approved' => false]);

        return to_route('dashboard.comments.index')
            ->with('success', 'মন্তব্য বাতিল করা হয়েছে।');
    }

    /**
     * Delete a comment (soft delete)
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('moderate', $comment);

        $comment->delete();

        return to_route('dashboard.comments.index')
            ->with('success', 'মন্তব্য মুছে ফেলা হয়েছে।');
    }
}
