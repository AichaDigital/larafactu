<?php

declare(strict_types=1);

define('AUTH_BASE_URL', 'https://larafactu.test');

describe('Login Page', function () {
    it('loads the login page successfully', function () {
        $page = $this->visit(AUTH_BASE_URL.'/login');

        $page->assertSee('Iniciar sesion');
    });

    it('displays email and password fields', function () {
        $page = $this->visit(AUTH_BASE_URL.'/login');

        $page->assertPresent('input[type="email"]')
            ->assertPresent('input[type="password"]');
    });

    it('has remember me checkbox', function () {
        $page = $this->visit(AUTH_BASE_URL.'/login');

        $page->assertSee('Recordarme');
    });

    it('has link to register page', function () {
        $page = $this->visit(AUTH_BASE_URL.'/login');

        $page->assertSee('Registrate');
    });

    it('has link to forgot password', function () {
        $page = $this->visit(AUTH_BASE_URL.'/login');

        $page->assertSeeLink('Olvidaste tu contrasena?');
    });
});

describe('Register Page', function () {
    it('loads the register page successfully', function () {
        $page = $this->visit(AUTH_BASE_URL.'/register');

        $page->assertSee('Crear cuenta');
    });

    it('displays registration form fields', function () {
        $page = $this->visit(AUTH_BASE_URL.'/register');

        $page->assertPresent('input[type="text"]')
            ->assertPresent('input[type="email"]')
            ->assertPresent('input[type="password"]');
    });

    it('has terms acceptance checkbox', function () {
        $page = $this->visit(AUTH_BASE_URL.'/register');

        $page->assertSee('terminos');
    });

    it('has link to login page', function () {
        $page = $this->visit(AUTH_BASE_URL.'/register');

        $page->assertSee('Inicia sesion');
    });
});

describe('Forgot Password Page', function () {
    it('loads the forgot password page successfully', function () {
        $page = $this->visit(AUTH_BASE_URL.'/forgot-password');

        $page->assertSee('Recuperar contrasena');
    });

    it('displays email field', function () {
        $page = $this->visit(AUTH_BASE_URL.'/forgot-password');

        $page->assertPresent('input[type="email"]');
    });
});
