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
        $theme = $this->resolveTheme($request);
        $locale = $this->resolveLocale($request);
        $timezone = $this->resolveTimezone($request);

        // Store in session for Blade templates
        session(['theme' => $theme]);
        session(['locale' => $locale]);
        session(['timezone' => $timezone]);

        // Apply locale
        App::setLocale($locale);

        // Apply timezone
        config(['app.timezone' => $timezone]);

        return $next($request);
    }

    /**
     * Resolve the theme to use.
     */
    private function resolveTheme(Request $request): string
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

        // 2. Session/cookie value
        $sessionTheme = session('theme');
        if ($sessionTheme && UserPreference::isValidTheme($sessionTheme)) {
            return $sessionTheme;
        }

        // 3. Default based on prefers-color-scheme header (if available)
        $prefersDark = $request->header('Sec-CH-Prefers-Color-Scheme') === 'dark';

        return $prefersDark ? 'abyss' : UserPreference::DEFAULT_THEME;
    }

    /**
     * Resolve the locale to use.
     */
    private function resolveLocale(Request $request): string
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

        // 3. Default
        return UserPreference::DEFAULT_LOCALE;
    }

    /**
     * Resolve the timezone to use.
     */
    private function resolveTimezone(Request $request): string
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

        // 3. Default
        return UserPreference::DEFAULT_TIMEZONE;
    }
}
