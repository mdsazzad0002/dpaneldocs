<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'is_admin' => $user->hasRole('admin'),
                    'can_manage_documentation' => $user->can('manage_documentation'),
                    'can_manage_versions' => $user->can('manage_versions'),
                    'can_manage_categories' => $user->can('manage_categories'),
                    'can_manage_users' => $user->can('manage_users'),
                    'can_manage_roles' => $user->can('manage_roles'),
                ]) : null,
            ],
        ];
    }
}
