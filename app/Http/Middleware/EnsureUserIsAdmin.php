<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets staff (any user with a role) into the admin panel. What they can do
 * inside it is decided per route by the `can:<permission>` middleware.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isStaff()) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
