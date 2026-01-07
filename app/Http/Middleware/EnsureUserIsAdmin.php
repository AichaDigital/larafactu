<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the authenticated user has admin privileges.
 *
 * ADR-004: Uses canAccessAdmin() which checks user_type (STAFF) or is_superadmin.
 *
 * This middleware should be used after 'auth' middleware.
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Must be authenticated
        if (! $user) {
            abort(403, 'Debes iniciar sesión.');
        }

        // Account must be active
        if (! $user->isAccountActive()) {
            abort(403, 'Tu cuenta está suspendida o inactiva.');
        }

        // Must have admin access (STAFF type or superadmin)
        if (! $user->canAccessAdmin()) {
            abort(403, 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
