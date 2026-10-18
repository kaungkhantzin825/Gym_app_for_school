<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the whole /admin area.
 *
 * Delegates to User::canAccessAdmin() so the bootstrap and ADMIN_EMAILS rules
 * live in exactly one place.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('admin.login'));
        }

        abort_unless($user->canAccessAdmin(), 403, 'This account is not an administrator.');

        return $next($request);
    }
}
