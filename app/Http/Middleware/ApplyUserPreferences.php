<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UserPreference;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Apply user preferences (theme, locale, timezone) to the request.
 *
 * ADR-005: User preferences middleware.
 *
 * Priority:
 * 1. Authenticated user: read from user_preferences table
 * 2. Anonymous user: read from session/cookie
 * 3. No preference: use defaults based on prefers-color-scheme
 */
class ApplyUserPreferences
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only set session values if user has explicit preferences or session already has them
        $theme = $this->resolveTheme($request);
        $locale = $this->resolveLocale($request);
        $timezone = $this->resolveTimezone($request);

        // Store in session for Blade templates (but only if we have a valid preference source)
        if ($theme !== null) {
            session(['theme' => $theme]);
        }
        if ($locale !== null) {
            session(['locale' => $locale]);
        }
        if ($timezone !== null) {
            session(['timezone' => $timezone]);
        }

        // Apply locale
        App::setLocale($locale ?? UserPreference::DEFAULT_LOCALE);

        // Apply timezone
        config(['app.timezone' => $timezone ?? UserPreference::DEFAULT_TIMEZONE]);

        return $next($request);
    }

    /**
     * Resolve the theme to use.
     * Returns null if no explicit preference exists (let client-side detect system theme).
     */
    private function resolveTheme(Request $request): ?string
    {
        // 1. Authenticated user with preferences
        if ($request->user()) {
            try {
                $preferences = $request->user()->preferences;
                if ($preferences && UserPreference::isValidTheme($preferences->theme)) {
                    return $preferences->theme;
                }
            } catch (\Exception $e) {
                // Table may not exist yet - graceful fallback
            }
        }

        // 2. Session/cookie value (already set by user interaction)
        $sessionTheme = session('theme');
        if ($sessionTheme && UserPreference::isValidTheme($sessionTheme)) {
            return $sessionTheme;
        }

        // 3. No explicit preference - return null to let JavaScript detect system theme
        return null;
    }

    /**
     * Resolve the locale to use.
     * Returns null if no explicit preference exists (will use default).
     */
    private function resolveLocale(Request $request): ?string
    {
        // 1. Authenticated user with preferences
        if ($request->user()) {
            try {
                $preferences = $request->user()->preferences;
                if ($preferences && UserPreference::isValidLocale($preferences->locale)) {
                    return $preferences->locale;
                }
            } catch (\Exception $e) {
                // Table may not exist yet - graceful fallback
            }
        }

        // 2. Session value
        $sessionLocale = session('locale');
        if ($sessionLocale && UserPreference::isValidLocale($sessionLocale)) {
            return $sessionLocale;
        }

        // 3. No explicit preference
        return null;
    }

    /**
     * Resolve the timezone to use.
     * Returns null if no explicit preference exists (will use default).
     */
    private function resolveTimezone(Request $request): ?string
    {
        // 1. Authenticated user with preferences
        if ($request->user()) {
            try {
                $preferences = $request->user()->preferences;
                if ($preferences && ! empty($preferences->timezone)) {
                    return $preferences->timezone;
                }
            } catch (\Exception $e) {
                // Table may not exist yet - graceful fallback
            }
        }

        // 2. Session value
        $sessionTimezone = session('timezone');
        if ($sessionTimezone) {
            return $sessionTimezone;
        }

        // 3. No explicit preference
        return null;
    }
}
