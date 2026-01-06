<?php

declare(strict_types=1);

/**
 * Browser tests for authentication pages.
 *
 * These tests use Pest Browser plugin (Playwright) to verify
 * that auth pages render correctly with all expected elements.
 */
describe('Login Page', function () {
    it('loads the login page successfully', function () {
        $page = visit('https://larafactu.test/login');

        $page->assertTitleContains('Iniciar sesion');
    });

    it('displays email and password fields', function () {
        $page = visit('https://larafactu.test/login');

        $page->assertPresent('input[type="email"]')
            ->assertPresent('input[type="password"]');
    });

    it('has remember me checkbox', function () {
        $page = visit('https://larafactu.test/login');

        $page->assertPresent('input[type="checkbox"]#remember');
    });

    it('has link to register page', function () {
        $page = visit('https://larafactu.test/login');

        $page->assertPresent('a[href*="register"]');
    });

    it('has link to forgot password', function () {
        $page = visit('https://larafactu.test/login');

        $page->assertPresent('a[href*="forgot-password"]');
    });
});

describe('Register Page', function () {
    it('loads the register page successfully', function () {
        $page = visit('https://larafactu.test/register');

        $page->assertTitleContains('Crear cuenta');
    });

    it('displays registration form fields', function () {
        $page = visit('https://larafactu.test/register');

        $page->assertPresent('input[type="text"]')
            ->assertPresent('input[type="email"]')
            ->assertPresent('input[type="password"]');
    });

    it('has terms acceptance checkbox', function () {
        $page = visit('https://larafactu.test/register');

        $page->assertPresent('input[type="checkbox"]');
    });

    it('has link to login page', function () {
        $page = visit('https://larafactu.test/register');

        $page->assertPresent('a[href*="login"]');
    });
});

describe('Forgot Password Page', function () {
    it('loads the forgot password page successfully', function () {
        $page = visit('https://larafactu.test/forgot-password');

        $page->assertTitleContains('Recuperar');
    });

    it('displays email field', function () {
        $page = visit('https://larafactu.test/forgot-password');

        $page->assertPresent('input[type="email"]');
    });
});
