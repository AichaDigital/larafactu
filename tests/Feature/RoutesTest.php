<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Tests de rutas - TDD para verificar que las paginas cargan correctamente.
 *
 * Cada test verifica:
 * 1. HTTP status correcto
 * 2. Texto esperado en la pagina (titulo, breadcrumb, contenido clave)
 */
describe('Public Routes', function () {
    it('loads home page', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
    });

    it('loads login page with correct title', function () {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Iniciar sesion');
    });

    it('loads register page with correct title', function () {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Crear cuenta');
    });

    it('loads forgot password page with correct title', function () {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Recuperar contrasena');
    });

    it('loads reset password page with token', function () {
        $response = $this->get('/reset-password/test-token?email=test@example.com');

        $response->assertStatus(200);
        $response->assertSee('Restablecer contrasena');
    });

    it('redirects guest from dashboard to login', function () {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    });
});

describe('Authenticated Routes', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('loads dashboard with welcome text', function () {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    });

    it('loads profile page', function () {
        $response = $this->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Mi perfil');
    });

    it('loads invoices index', function () {
        $response = $this->get('/invoices');

        $response->assertStatus(200);
        $response->assertSee('Facturas');
    });

    it('loads invoice create page', function () {
        $response = $this->get('/invoices/create');

        $response->assertStatus(200);
        $response->assertSee('Nueva factura');
    });

    it('loads customers index', function () {
        $response = $this->get('/customers');

        $response->assertStatus(200);
        $response->assertSee('Clientes');
    });

    it('loads customer create page', function () {
        $response = $this->get('/customers/create');

        $response->assertStatus(200);
        $response->assertSee('Nuevo cliente');
    });

    it('loads customer edit page', function () {
        $customer = User::factory()->create();

        $response = $this->get("/customers/{$customer->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Editar cliente');
    });

    it('loads articles index', function () {
        $response = $this->get('/articles');

        $response->assertStatus(200);
        $response->assertSee('Articulos');
    });
});

describe('Admin Routes', function () {
    it('redirects guest from admin to login', function () {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    });

    it('denies non-admin access to admin dashboard', function () {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertStatus(403);
    });

    it('allows admin access to admin dashboard', function () {
        // ADR-004: Use staff() factory state
        $admin = User::factory()->staff()->create();
        $this->actingAs($admin);

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Panel de administracion');
    });

    it('loads admin users page for admin', function () {
        // ADR-004: Use staff() factory state
        $admin = User::factory()->staff()->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Usuarios');
    });
});

describe('Auth Actions', function () {
    it('can request password reset link', function () {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        Livewire::test(\App\Livewire\Auth\ForgotPassword::class)
            ->set('email', 'reset@example.com')
            ->call('sendResetLink')
            ->assertSet('emailSent', true);
    });

    it('shows error for non-existent email on password reset', function () {
        Livewire::test(\App\Livewire\Auth\ForgotPassword::class)
            ->set('email', 'nonexistent@example.com')
            ->call('sendResetLink')
            ->assertHasErrors('email');
    });

    it('can register via Livewire and reach dashboard', function () {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('terms', true)
            ->call('register')
            ->assertRedirect('/dashboard');

        // Verify user was created and is authenticated
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Test User',
        ]);
        $this->assertAuthenticated();

        // Access dashboard
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    });

    it('can login via Livewire and reach dashboard', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Test Livewire login component
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect('/dashboard');

        // Verify user is authenticated after Livewire action
        $this->assertAuthenticatedAs($user);

        // Access dashboard
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    });

    it('can logout', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    });
});
