<?php

declare(strict_types=1);

/**
 * Browser tests for authentication pages.
 *
 * These tests use Pest Browser plugin (Playwright) to verify
 * that auth pages render correctly with all expected elements.
 *
 * Note: These tests require the local dev server to be running.
 * They are excluded from CI (see phpunit.xml).
 */

// Use environment variable or fallback to local dev URL
define('AUTH_BASE_URL', env('BROWSER_TEST_URL', 'http://larafactu.test'));

// Increase default timeout for Livewire hydration
define('PAGE_LOAD_TIMEOUT', 10000);

describe('Login Page', function () {
    it('loads the login page successfully', function () {
        $this->visit(AUTH_BASE_URL.'/login')
            ->waitForText('Iniciar sesion', PAGE_LOAD_TIMEOUT)
            ->assertSee('Iniciar sesion');
    });

    it('displays email and password fields', function () {
        $this->visit(AUTH_BASE_URL.'/login')
            ->waitForText('Iniciar sesion', PAGE_LOAD_TIMEOUT)
            ->assertPresent('input[type="email"]')
            ->assertPresent('input[type="password"]');
    });

    it('has remember me checkbox', function () {
        $this->visit(AUTH_BASE_URL.'/login')
            ->waitForText('Recordarme', PAGE_LOAD_TIMEOUT)
            ->assertSee('Recordarme');
    });

    it('has link to register page', function () {
        $this->visit(AUTH_BASE_URL.'/login')
            ->waitForText('Registrate', PAGE_LOAD_TIMEOUT)
            ->assertSee('Registrate');
    });

    it('has link to forgot password', function () {
        $this->visit(AUTH_BASE_URL.'/login')
            ->waitForText('Olvidaste tu contrasena?', PAGE_LOAD_TIMEOUT)
            ->assertSeeLink('Olvidaste tu contrasena?');
    });
});

describe('Register Page', function () {
    it('loads the register page successfully', function () {
        $this->visit(AUTH_BASE_URL.'/register')
            ->waitForText('Crear cuenta', PAGE_LOAD_TIMEOUT)
            ->assertSee('Crear cuenta');
    });

    it('displays registration form fields', function () {
        $this->visit(AUTH_BASE_URL.'/register')
            ->waitForText('Crear cuenta', PAGE_LOAD_TIMEOUT)
            ->assertPresent('input[type="text"]')
            ->assertPresent('input[type="email"]')
            ->assertPresent('input[type="password"]');
    });

    it('has terms acceptance checkbox', function () {
        $this->visit(AUTH_BASE_URL.'/register')
            ->waitForText('terminos', PAGE_LOAD_TIMEOUT)
            ->assertSee('terminos');
    });

    it('has link to login page', function () {
        $this->visit(AUTH_BASE_URL.'/register')
            ->waitForText('Inicia sesion', PAGE_LOAD_TIMEOUT)
            ->assertSee('Inicia sesion');
    });
});

describe('Forgot Password Page', function () {
    it('loads the forgot password page successfully', function () {
        $this->visit(AUTH_BASE_URL.'/forgot-password')
            ->waitForText('Recuperar contrasena', PAGE_LOAD_TIMEOUT)
            ->assertSee('Recuperar contrasena');
    });

    it('displays email field', function () {
        $this->visit(AUTH_BASE_URL.'/forgot-password')
            ->waitForText('Recuperar contrasena', PAGE_LOAD_TIMEOUT)
            ->assertPresent('input[type="email"]');
    });
});
