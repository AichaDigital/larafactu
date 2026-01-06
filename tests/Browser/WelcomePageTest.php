<?php

declare(strict_types=1);

/**
 * Browser tests for the welcome/landing page.
 *
 * These tests use Pest Browser plugin (Playwright) to verify
 * that the welcome page renders correctly with all expected elements.
 */
describe('Welcome Page', function () {
    it('loads the welcome page successfully', function () {
        $page = visit('https://larafactu.test/');

        $page->assertTitleContains('Larafactu');
    });

    it('displays the hero section with main heading', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Facturacion Electronica Moderna');
    });

    it('shows pre-production badge', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Pre-Produccion');
    });

    it('displays main features', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Caracteristicas Principales')
            ->assertSee('Facturacion Completa')
            ->assertSee('Verifactu AEAT');
    });

    it('shows tech stack badges', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Stack Tecnologico')
            ->assertSee('Laravel 12')
            ->assertSee('DaisyUI 5');
    });

    it('displays packages section', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Paquetes Modulares')
            ->assertSee('aichadigital/larabill');
    });

    it('has login and register links for guests', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Iniciar Sesion')
            ->assertSee('Registrarse');
    });

    it('has footer with company info', function () {
        $page = visit('https://larafactu.test/');

        $page->assertSee('Aicha Digital');
    });
});

describe('Theme Toggle', function () {
    it('has theme toggle button', function () {
        $page = visit('https://larafactu.test/');

        $page->assertPresent('#theme-toggle');
    });
});
