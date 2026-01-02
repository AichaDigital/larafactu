<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User preferences for theme, locale, timezone, etc.
 *
 * ADR-005: System of persistent user preferences.
 */
class UserPreference extends Model
{
    public const AVAILABLE_THEMES = ['cupcake', 'corporate', 'abyss', 'sunset'];

    public const AVAILABLE_LOCALES = ['es', 'en', 'ca', 'eu', 'gl'];

    public const DEFAULT_THEME = 'cupcake';

    public const DEFAULT_LOCALE = 'es';

    public const DEFAULT_TIMEZONE = 'Europe/Madrid';

    protected $fillable = [
        'user_id',
        'locale',
        'theme',
        'timezone',
        'notifications',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notifications' => 'array',
        ];
    }

    /**
     * Get the user that owns these preferences.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create preferences for a user.
     */
    public static function forUser(User $user): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id],
            [
                'locale' => self::DEFAULT_LOCALE,
                'theme' => self::DEFAULT_THEME,
                'timezone' => self::DEFAULT_TIMEZONE,
            ]
        );
    }

    /**
     * Check if a theme is valid.
     */
    public static function isValidTheme(string $theme): bool
    {
        return in_array($theme, self::AVAILABLE_THEMES, true);
    }

    /**
     * Check if a locale is valid.
     */
    public static function isValidLocale(string $locale): bool
    {
        return in_array($locale, self::AVAILABLE_LOCALES, true);
    }
}
