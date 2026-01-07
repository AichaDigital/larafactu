<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure installer directory has been removed.
 *
 * After installation, the installer/ directory should be deleted for security.
 * This middleware provides warnings and eventually blocks access if not removed.
 */
class EnsureInstallerRemoved
{
    /**
     * Grace period in hours after installation completion
     */
    private const GRACE_PERIOD_HOURS = 24;

    /**
     * How often to show the warning (in requests)
     */
    private const WARNING_FREQUENCY = 10;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installerPath = base_path('installer');

        // Check if installer directory exists
        if (! is_dir($installerPath)) {
            return $next($request);
        }

        // Check if .done file exists (installation completed)
        $doneFile = $installerPath.'/.done';

        if (! file_exists($doneFile)) {
            // Installation not complete - allow access (might be first setup)
            return $next($request);
        }

        // Read completion data
        $doneData = json_decode(file_get_contents($doneFile), true);
        $completedAt = $doneData['completed_at'] ?? 0;
        $hoursSinceInstall = (time() - $completedAt) / 3600;

        // If grace period expired, block access
        if ($hoursSinceInstall > self::GRACE_PERIOD_HOURS) {
            return $this->blockAccess($request);
        }

        // Within grace period - show warning occasionally
        $requestCount = session('installer_warning_count', 0) + 1;
        session(['installer_warning_count' => $requestCount]);

        if ($requestCount % self::WARNING_FREQUENCY === 1) {
            $hoursRemaining = round(self::GRACE_PERIOD_HOURS - $hoursSinceInstall, 1);

            session()->flash('installer_warning', sprintf(
                'Por seguridad, elimine el directorio installer/. Tiempo restante: %.1f horas.',
                max(0, $hoursRemaining)
            ));
        }

        return $next($request);
    }

    /**
     * Block access due to installer not removed
     */
    private function blockAccess(Request $request): Response
    {
        // For API requests, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Application blocked',
                'message' => 'Please remove the installer directory for security.',
            ], 503);
        }

        // For web requests, show blocking page
        return response()->view('errors.installer-not-removed', [
            'installerPath' => base_path('installer'),
        ], 503);
    }
}
