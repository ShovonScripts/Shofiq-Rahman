<?php

// app/Http/Controllers/Dashboard/UserController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->select('id', 'name', 'email', 'role', 'is_active', 'email_verified_at', 'created_at')
            ->paginate(15)
            ->appends(['q' => $q]);

        return Inertia::render('Dashboard/Users/Index', [
            'filters' => ['q' => $q],
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,author,reader'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->role = $data['role'];
        $user->is_active = true;
        $user->save();

        return back()->with('success', 'User created.');
    }

    public function updateRole(Request $request, User $user)
    {
        // সেলফ-ডিমোশন/প্রমোশন সেফগার্ড (ইচ্ছা হলে)
        if ($user->id === $request->user()->id) {
            return back()->with('error', "You can't change your own role.");
        }

        $data = $request->validate([
            'role' => ['required', 'in:admin,author,reader'],
        ]);

        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Role updated.');
    }

    public function toggleActive(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', "You can't deactivate yourself.");
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password reset.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', "You can't delete yourself.");
        }
        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
