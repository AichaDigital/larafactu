<?php

declare(strict_types=1);

namespace Installer\I18n;

/**
 * Simple translator for the installer.
 */
class Translator
{
    private string $locale;

    private array $translations = [];

    private array $loadedLocales = [];

    public function __construct(?string $locale = null)
    {
        $this->locale = $locale ?? $this->detectLocale();
        $this->loadTranslations($this->locale);
    }

    /**
     * Detect locale from cookie or browser
     */
    private function detectLocale(): string
    {
        // Check cookie first
        if (isset($_COOKIE[LANG_COOKIE_NAME])) {
            $cookieLocale = $_COOKIE[LANG_COOKIE_NAME];
            if (in_array($cookieLocale, ['es', 'en'])) {
                return $cookieLocale;
            }
        }

        // Check browser Accept-Language
        $browserLang = $this->detectBrowserLanguage();

        if ($browserLang !== null) {
            return $browserLang;
        }

        return 'es'; // Default to Spanish
    }

    /**
     * Detect language from browser Accept-Language header
     */
    private function detectBrowserLanguage(): ?string
    {
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

        if (empty($accept)) {
            return null;
        }

        // Parse Accept-Language header
        $langs = [];
        foreach (explode(',', $accept) as $part) {
            $part = trim($part);
            $q = 1.0;

            if (strpos($part, ';q=') !== false) {
                [$lang, $qPart] = explode(';q=', $part);
                $q = (float) $qPart;
            } else {
                $lang = $part;
            }

            // Get just the language code (not region)
            $lang = strtolower(substr($lang, 0, 2));
            $langs[$lang] = $q;
        }

        arsort($langs);

        // Check for supported languages
        foreach (array_keys($langs) as $lang) {
            if (in_array($lang, ['es', 'en'])) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Load translations for a locale
     */
    public function loadTranslations(string $locale): void
    {
        if (isset($this->loadedLocales[$locale])) {
            return;
        }

        $file = INSTALLER_ROOT.'/public/assets/i18n/'.$locale.'.json';

        if (! file_exists($file)) {
            $file = INSTALLER_ROOT.'/public/assets/i18n/es.json';
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if ($data !== null) {
            $this->translations[$locale] = $this->flattenArray($data);
            $this->loadedLocales[$locale] = true;
        }
    }

    /**
     * Flatten nested array to dot notation keys
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix.'.'.$key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Set current locale
     */
    public function setLocale(string $locale): void
    {
        if (! in_array($locale, ['es', 'en'])) {
            $locale = 'es';
        }

        $this->locale = $locale;
        $this->loadTranslations($locale);
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Translate a key
     */
    public function translate(string $key, array $replace = []): string
    {
        $this->loadTranslations($this->locale);

        $translation = $this->translations[$this->locale][$key] ?? $key;

        // Replace placeholders
        foreach ($replace as $placeholder => $value) {
            $translation = str_replace(':'.$placeholder, (string) $value, $translation);
        }

        return $translation;
    }

    /**
     * Check if translation exists
     */
    public function has(string $key): bool
    {
        $this->loadTranslations($this->locale);

        return isset($this->translations[$this->locale][$key]);
    }

    /**
     * Get all translations for current locale
     */
    public function all(): array
    {
        $this->loadTranslations($this->locale);

        return $this->translations[$this->locale] ?? [];
    }
}
