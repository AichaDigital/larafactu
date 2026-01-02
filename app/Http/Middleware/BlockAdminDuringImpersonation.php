<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to block admin routes during impersonation.
 *
 * When an admin is impersonating a user, they should not be able to access
 * admin-only routes to maintain the impersonation experience.
 *
 * Exception: The impersonation stop route is allowed.
 *
 * @see ADR-004 Authorization System
 */
class BlockAdminDuringImpersonation
{
    public function __construct(
        private readonly ImpersonationService $impersonationService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow if not impersonating
        if (! $this->impersonationService->isImpersonating()) {
            return $next($request);
        }

        // Allow the stop impersonation route
        if ($request->routeIs('admin.impersonate.stop')) {
            return $next($request);
        }

        // Block other admin routes during impersonation
        return redirect()->route('dashboard')
            ->with('warning', 'No puedes acceder al panel de administracion mientras impersonas a un usuario.');
    }
}
