<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     *   ensure_admin:admin  → allow admin & author (since author has same power)
     *   ensure_admin:author → allow admin & author
     *   ensure_admin        → allow admin & author
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        // 🔒 Must be logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::user();

        // 🧱 Block inactive users
        if (!is_null($user->is_active) && !$user->is_active) {
            abort(403, 'Your account is not active.');
        }

        // 🎯 Role logic
        // Both 'author' and 'admin' have full dashboard access
        if (in_array($role, ['admin', 'author', null], true)) {
            if (!$user->isAuthor() && !$user->isAdmin()) {
                abort(403, 'Access denied.');
            }
        }

        // (Optional) If you ever introduce limited roles later
        // you can handle them here.

        return $next($request);
    }
}
