<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Admin User Create Tests - TDD
|--------------------------------------------------------------------------
|
| Tests for the admin user creation functionality.
|
*/

beforeEach(function () {
    // Create admin user
    $this->admin = User::factory()->create(['email' => 'admin@testdomain.com']);
    config(['app.admin_domains' => 'testdomain.com']);

    // Create regular user (non-admin)
    $this->regularUser = User::factory()->create(['email' => 'user@example.com']);
});

describe('Admin User Create Access', function () {

    it('allows admin to access user create page', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertSee('Crear Usuario');
    });

    it('denies non-admin access to user create page', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.users.create'));

        $response->assertForbidden();
    });

    it('redirects guest to login', function () {
        $response = $this->get(route('admin.users.create'));

        $response->assertRedirect(route('login'));
    });

});

describe('Admin User Create Form', function () {

    it('displays required form fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertSee('Nombre');
        $response->assertSee('Email');
        $response->assertSee('Contrasena');
    });

    it('displays optional billing fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertSee('Nombre comercial');
        $response->assertSee('Tipo de entidad');
    });

    it('displays delegation fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertSee('Tipo de relacion');
    });

});

describe('Admin User Create Validation', function () {

    it('requires name field', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('email', 'newuser@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertHasErrors(['name' => 'required']);
    });

    it('requires email field', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'New User')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertHasErrors(['email' => 'required']);
    });

    it('requires valid email format', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'New User')
            ->set('email', 'invalid-email')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertHasErrors(['email' => 'email']);
    });

    it('requires unique email', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'New User')
            ->set('email', $this->regularUser->email)
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertHasErrors(['email' => 'unique']);
    });

    it('requires password field', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->call('create')
            ->assertHasErrors(['password' => 'required']);
    });

    it('requires password confirmation to match', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'DifferentPassword!')
            ->call('create')
            ->assertHasErrors(['password' => 'confirmed']);
    });

});

describe('Admin User Create Success', function () {

    it('creates user with required fields only', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);
    });

    it('creates user with all fields', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'Complete User')
            ->set('email', 'complete@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('display_name', 'Complete Trading Name')
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'name' => 'Complete User',
            'email' => 'complete@example.com',
            'display_name' => 'Complete Trading Name',
        ]);
    });

    it('creates delegated user with parent', function () {
        $parentUser = User::factory()->create();

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'Delegated User')
            ->set('email', 'delegated@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('relationship_type', 1) // DELEGATED
            ->set('parent_user_id', $parentUser->id)
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'name' => 'Delegated User',
            'email' => 'delegated@example.com',
            'parent_user_id' => $parentUser->id,
            'relationship_type' => 1,
        ]);
    });

    it('hashes password correctly', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'Password Test')
            ->set('email', 'password@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertHasNoErrors();

        $user = User::where('email', 'password@example.com')->first();
        expect(\Illuminate\Support\Facades\Hash::check('Password123!', $user->password))->toBeTrue();
    });

    it('sets flash message on success', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserCreate::class)
            ->set('name', 'Flash Test')
            ->set('email', 'flash@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->call('create')
            ->assertSessionHas('success');
    });

});
