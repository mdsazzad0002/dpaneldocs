<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'manage_documentation',
            'manage_versions',
            'manage_categories',
            'manage_users',
            'manage_roles',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions($permissions);

        $editor = Role::findOrCreate('editor', 'web');
        $editor->syncPermissions(['manage_documentation', 'manage_versions', 'manage_categories']);

        Role::findOrCreate('contributor', 'web');

        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->where('is_admin', true)->get(['id'])->each(
                fn ($row) => \App\Models\User::find($row->id)?->assignRole('admin')
            );

            DB::table('users')->where('is_admin', false)->orWhereNull('is_admin')->get(['id'])->each(function ($row) {
                $user = \App\Models\User::find($row->id);
                if ($user && $user->roles()->count() === 0) {
                    $user->assignRole('contributor');
                }
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        DB::table('users')
            ->whereIn('id', DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'admin')
                ->pluck('model_has_roles.model_id'))
            ->update(['is_admin' => true]);
    }
};
