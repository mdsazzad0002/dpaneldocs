<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const PROTECTED_ROLES = ['admin'];

    public function index(): Response
    {
        $permissions = Permission::orderBy('name')->pluck('name');

        $roles = Role::with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'user_count' => $role->users()->count(),
                'protected' => in_array($role->name, self::PROTECTED_ROLES, true),
            ]);

        return Inertia::render('Backend/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('roles', 'name')],
        ]);

        Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        return back()->with('success', "Role '{$validated['name']}' created.");
    }

    public function updatePermissions(Request $request, string $id): RedirectResponse
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Permissions for '{$role->name}' updated.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->withErrors(['role' => "The '{$role->name}' role cannot be deleted."]);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['role' => "Cannot delete '{$role->name}' while users are assigned to it."]);
        }

        $role->delete();

        return back()->with('success', "Role '{$role->name}' deleted.");
    }
}
