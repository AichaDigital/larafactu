<?php

declare(strict_types=1);

const BASE_URL = 'https://larafactu.test';

describe('Welcome Page', function () {
    it('loads the welcome page successfully', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Larafactu');
    });

    it('displays the hero section with main heading', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Facturacion Electronica Moderna');
    });

    it('shows pre-production badge', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Pre-Produccion');
    });

    it('displays main features', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Caracteristicas Principales')
            ->assertSee('Facturacion Completa')
            ->assertSee('Verifactu AEAT');
    });

    it('shows tech stack badges', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Stack Tecnologico')
            ->assertSee('Laravel 12')
            ->assertSee('DaisyUI 5');
    });

    it('displays packages section', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Paquetes Modulares')
            ->assertSee('aichadigital/larabill');
    });

    it('has login and register links for guests', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Iniciar Sesion')
            ->assertSee('Registrarse');
    });

    it('has footer with company info', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertSee('Aicha Digital');
    });
});

describe('Theme Toggle', function () {
    it('has theme toggle button', function () {
        $page = $this->visit(BASE_URL.'/');

        $page->assertPresent('#theme-toggle');
    });
});
