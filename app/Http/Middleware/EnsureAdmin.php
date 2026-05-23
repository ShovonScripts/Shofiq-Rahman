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
     *   ensure_admin:admin  → allow ONLY admin
     *   ensure_admin:author → allow admin & author
     *   ensure_admin        → allow admin & author
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            abort(403, 'Your account is not active.');
        }

        if ($role === 'admin') {
            if (! $user->isAdmin()) {
                abort(403, 'Access denied. Admins only.');
            }
        } else {
            if (! $user->isAuthor() && ! $user->isAdmin()) {
                abort(403, 'Access denied.');
            }
        }

        return $next($request);
    }
}
