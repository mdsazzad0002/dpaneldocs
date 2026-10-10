<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Backend/Roles/Index', [
            'roles' => Role::withCount('users')->orderByDesc('is_system')->orderBy('name')->get(),
            'groups' => Permissions::GROUPS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Role::create($data + ['slug' => $this->uniqueSlug($data['name'])]);

        return back()->with('status', "Role \"{$data['name']}\" created.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role);

        // The Administrator role always keeps every permission.
        if ($role->is_system) {
            unset($data['permissions']);
        }

        $role->update($data);

        return back()->with('status', "Role \"{$role->name}\" saved.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['role' => 'The Administrator role cannot be deleted.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Move the staff with this role to another role first.']);
        }

        $role->delete();

        return back()->with('status', "Role \"{$role->name}\" deleted.");
    }

    /**
     * @return array{name: string, description: ?string, permissions: array<int, string>}
     */
    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(Permissions::all())],
        ]);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => array_values(array_unique($data['permissions'] ?? [])),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $i = 2;

        while (Role::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
