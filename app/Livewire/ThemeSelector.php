<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\UserPreference;
use Livewire\Component;

/**
 * Theme selector component - simplified to light/dark toggle.
 *
 * ADR-005: Persists theme preference for authenticated users.
 * Respects system preference (prefers-color-scheme) when no preference set.
 */
class ThemeSelector extends Component
{
    public string $currentTheme = 'cupcake';

    public function mount(): void
    {
        $this->currentTheme = session('theme', UserPreference::DEFAULT_THEME);
    }

    /**
     * Toggle between light and dark theme.
     */
    public function toggleTheme(): void
    {
        $isDark = in_array($this->currentTheme, ['abyss', 'sunset'], true);
        $newTheme = $isDark ? 'cupcake' : 'abyss';

        $this->setTheme($newTheme);
    }

    /**
     * Set the theme and persist it.
     */
    public function setTheme(string $theme): void
    {
        if (! UserPreference::isValidTheme($theme)) {
            return;
        }

        $this->currentTheme = $theme;

        // Store in session for immediate use
        session(['theme' => $theme]);

        // Persist to database if authenticated
        if (auth()->check()) {
            $preferences = auth()->user()->getPreferences();
            $preferences->update(['theme' => $theme]);
        }

        // Dispatch browser event to update the DOM
        $this->dispatch('theme-changed', theme: $theme);
    }

    /**
     * Check if current theme is dark.
     */
    public function isDark(): bool
    {
        return in_array($this->currentTheme, ['abyss', 'sunset'], true);
    }

    public function render()
    {
        return view('livewire.theme-selector');
    }
}
