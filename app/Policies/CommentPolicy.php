<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function moderate(User $user, Comment $comment): bool
    {
        $parent = $comment->commentable;

        return $user->isAdmin() || (($parent instanceof Post || $parent instanceof Book)
            && (int) $parent->user_id === (int) $user->id);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true;
        }

        $ownerId = null;
        $parent = $comment->commentable;

        if ($parent instanceof Post || $parent instanceof Book) {
            $ownerId = $parent->user_id ?? null;
        }

        return $ownerId && $ownerId === $user->id;
    }
}
