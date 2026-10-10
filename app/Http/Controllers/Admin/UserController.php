<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('Backend/Users/Index', [
            'users' => User::with('role:id,name,slug')
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        User::create($data + ['email_verified_at' => now()]);

        return back()->with('status', "Account created for {$data['email']}.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        if ($this->wouldRemoveLastAdmin($user, $data['role_id'] ?? null)) {
            return back()->withErrors(['role_id' => 'At least one Administrator must remain.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('status', "Saved {$user->email}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account here. Use the Profile page.']);
        }

        if ($this->wouldRemoveLastAdmin($user, null)) {
            return back()->withErrors(['user' => 'At least one Administrator must remain.']);
        }

        $user->delete();

        return back()->with('status', "Deleted {$user->email}.");
    }

    private function wouldRemoveLastAdmin(User $user, mixed $newRoleId): bool
    {
        $adminRoleId = Role::where('slug', 'admin')->value('id');

        return $user->role_id === $adminRoleId
            && (int) $newRoleId !== $adminRoleId
            && User::where('role_id', $adminRoleId)->count() <= 1;
    }
}
