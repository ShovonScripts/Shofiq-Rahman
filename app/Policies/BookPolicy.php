<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function view(User $user, Book $book): bool
    {
        return $book->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Book $book): bool
    {
        return $book->user_id === $user->id;
    }

    public function delete(User $user, Book $book): bool
    {
        return $book->user_id === $user->id;
    }
}
