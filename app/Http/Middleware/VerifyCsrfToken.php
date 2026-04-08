<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * যেসব URI-তে CSRF যাচাই লাগবে না।
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
