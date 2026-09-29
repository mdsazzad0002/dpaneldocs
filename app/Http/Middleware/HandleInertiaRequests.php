<?php

namespace App\Http\Middleware;

use App\Models\Review;
use App\Models\SupportTicket;
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
        $isAdmin = $user?->hasRole('admin') ?? false;

        return [
            ...parent::share($request),
            'appName' => config('site.name'),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), ['is_admin' => $isAdmin]) : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'badges' => fn () => $isAdmin ? [
                'tickets' => SupportTicket::where('status', 'open')->count(),
                'reviews' => Review::where('status', 'pending')->count(),
            ] : [],
        ];
    }
}
