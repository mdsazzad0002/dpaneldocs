<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name,
            ]);

        return Inertia::render('Backend/Users/Index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function updateRole(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.']);
        }

        $user->syncRoles([$validated['role']]);

        return back()->with('success', "{$user->name}'s role set to {$validated['role']}.");
    }
}
