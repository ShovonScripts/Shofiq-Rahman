<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Book;
use App\Policies\PostPolicy;
use App\Policies\BookPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
        Book::class => BookPolicy::class,
    ];

    public function boot(): void
    {
        // no gates needed for now
    }
}
