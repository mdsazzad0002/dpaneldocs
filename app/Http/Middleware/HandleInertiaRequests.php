<?php

namespace App\Http\Middleware;

use App\Models\Comment;
use App\Models\Donation;
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
        $isStaff = $user?->isStaff() ?? false;

        return [
            ...parent::share($request),
            'appName' => config('site.name'),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'is_staff' => $isStaff,
                    'role' => $user->role?->name,
                ]) : null,
                'can' => $user ? $user->permissionMap() : [],
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'badges' => fn () => $isStaff ? array_filter([
                'tickets' => $user->hasPermission('tickets.manage') ? SupportTicket::where('status', 'open')->count() : null,
                'reviews' => $user->hasPermission('reviews.manage') ? Review::where('status', 'pending')->count() : null,
                'comments' => $user->hasPermission('comments.manage') ? Comment::where('status', 'pending')->count() : null,
                'donations' => $user->hasPermission('donations.manage') ? Donation::where('status', 'pending')->count() : null,
            ]) : [],
        ];
    }
}
