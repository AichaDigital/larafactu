<?php

declare(strict_types=1);

namespace Installer\Tests\Unit;

use Installer\I18n\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Translator class
 */
class TranslatorTest extends TestCase
{
    public function test_default_locale_is_spanish(): void
    {
        // Clear any existing cookie
        unset($_COOKIE['installer_locale']);
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

        $translator = new Translator;

        $this->assertEquals('es', $translator->getLocale());
    }

    public function test_can_set_locale(): void
    {
        $translator = new Translator;
        $translator->setLocale('en');

        $this->assertEquals('en', $translator->getLocale());
    }

    public function test_unsupported_locale_is_ignored(): void
    {
        $translator = new Translator;
        $translator->setLocale('fr'); // Not supported

        // Should remain at default
        $this->assertContains($translator->getLocale(), ['es', 'en']);
    }

    public function test_returns_key_when_translation_not_found(): void
    {
        $translator = new Translator;

        $result = $translator->get('nonexistent.key');

        $this->assertEquals('nonexistent.key', $result);
    }

    public function test_can_replace_placeholders(): void
    {
        $translator = new Translator('es');

        // Assuming this key exists in es.json with :minutes placeholder
        $result = $translator->get('wizard.time_remaining', ['minutes' => 30]);

        $this->assertStringContainsString('30', $result);
    }

    public function test_supported_locales_includes_spanish_and_english(): void
    {
        $translator = new Translator;
        $supported = $translator->getSupportedLocales();

        $this->assertContains('es', $supported);
        $this->assertContains('en', $supported);
    }

    public function test_detects_browser_language(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9,es;q=0.8';
        unset($_COOKIE['installer_locale']);

        $translator = new Translator;

        // Should detect English from browser
        $this->assertEquals('en', $translator->getLocale());

        // Cleanup
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }
}
