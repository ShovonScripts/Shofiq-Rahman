<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user): bool
    {
        return true; // লগইন হলেই
    }

    public function delete(User $user, Comment $comment): bool
    {
        // ১) নিজের কমেন্ট সে ডিলিট করতে পারবে
        if ($comment->user_id === $user->id) return true;

        // ২) পোস্ট/বইয়ের মালিকও ডিলিট করতে পারবে
        $ownerId = null;
        $parent = $comment->commentable;

        if ($parent instanceof Post || $parent instanceof Book) {
            $ownerId = $parent->user_id ?? null;
        }

        return $ownerId && $ownerId === $user->id;
    }
}
