<?php

declare(strict_types=1);

/**
 * Browser tests for the welcome/landing page.
 *
 * These tests use Pest Browser plugin (Playwright) to verify
 * that the welcome page renders correctly with all expected elements.
 *
 * Note: These tests require the local dev server to be running.
 * They are excluded from CI (see phpunit.xml).
 */

// Use environment variable or fallback to local dev URL
define('BASE_URL', env('BROWSER_TEST_URL', 'http://larafactu.test'));

// Increase default timeout for page load
define('WELCOME_PAGE_TIMEOUT', 10000);

describe('Welcome Page', function () {
    it('loads the welcome page successfully', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Larafactu', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Larafactu');
    });

    it('displays the hero section with main heading', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Facturacion Electronica Moderna', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Facturacion Electronica Moderna');
    });

    it('shows pre-production badge', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Pre-Produccion', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Pre-Produccion');
    });

    it('displays main features', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Caracteristicas Principales', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Caracteristicas Principales')
            ->assertSee('Facturacion Completa')
            ->assertSee('Verifactu AEAT');
    });

    it('shows tech stack badges', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Stack Tecnologico', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Stack Tecnologico')
            ->assertSee('Laravel 12')
            ->assertSee('DaisyUI 5');
    });

    it('displays packages section', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Paquetes Modulares', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Paquetes Modulares')
            ->assertSee('aichadigital/larabill');
    });

    it('has login and register links for guests', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Iniciar Sesion', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Iniciar Sesion')
            ->assertSee('Registrarse');
    });

    it('has footer with company info', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Aicha Digital', WELCOME_PAGE_TIMEOUT)
            ->assertSee('Aicha Digital');
    });
});

describe('Theme Toggle', function () {
    it('has theme toggle button', function () {
        $this->visit(BASE_URL.'/')
            ->waitForText('Larafactu', WELCOME_PAGE_TIMEOUT)
            ->assertPresent('#theme-toggle');
    });
});
