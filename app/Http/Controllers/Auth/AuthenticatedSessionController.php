<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;                 // ✅ import your concrete User model
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * Readers should NOT be sent to the dashboard (it’s protected),
     * so we redirect them to the homepage. Authors/Admins go to /dashboard.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();          // validates & attempts login
        $request->session()->regenerate(); // session fixation protection

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // If for any reason we didn't get a concrete User, fall back to home.
        if (!$user instanceof User) {
            return redirect()->to('/');
        }

        // Optional: block inactive users if you use is_active
        // if (property_exists($user, 'is_active') && !$user->is_active) {
        //     Auth::logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();
        //     return redirect()->route('login')->withErrors([
        //         'email' => 'Your account is not active.',
        //     ]);
        // }

        // ✅ Role-based redirect
        if ($user->isAdmin() || $user->isAuthor()) {
            // honour intended URL within dashboard for authors/admins
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // 🧭 Default for readers (or any other role): go to homepage
        return redirect()->to('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
