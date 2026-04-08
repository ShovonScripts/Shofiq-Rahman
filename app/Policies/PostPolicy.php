<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /** যে কেউ নিজের পোস্ট লিস্ট/শো দেখতে পারবে, ভিউ পাবলিক রাউটেই কন্ট্রোল করা আছে */
    public function view(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // লগইন করা থাকলেই চলবে
    }

    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    public function toggle(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }
}
