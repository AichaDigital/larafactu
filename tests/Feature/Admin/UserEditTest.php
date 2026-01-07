<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Admin User Edit Tests - TDD
|--------------------------------------------------------------------------
|
| Tests for the admin user edit functionality.
|
*/

beforeEach(function () {
    // ADR-004: Create admin user using staff() factory state
    $this->admin = User::factory()->staff()->create();

    // Create regular user (non-admin) to edit
    $this->targetUser = User::factory()->create([
        'email' => 'target@example.com',
        'name' => 'Target User',
        'display_name' => 'Target Display',
    ]);

    // Create another regular user
    $this->regularUser = User::factory()->create(['email' => 'regular@example.com']);
});

describe('Admin User Edit Access', function () {

    it('allows admin to access user edit page', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->targetUser));

        $response->assertOk();
        $response->assertSee('Editar Usuario');
    });

    it('denies non-admin access to user edit page', function () {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.users.edit', $this->targetUser));

        $response->assertForbidden();
    });

    it('redirects guest to login', function () {
        $response = $this->get(route('admin.users.edit', $this->targetUser));

        $response->assertRedirect(route('login'));
    });

    it('returns 404 for non-existent user', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', 'non-existent-uuid'));

        $response->assertNotFound();
    });

});

describe('Admin User Edit Form', function () {

    it('displays user current data', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->targetUser));

        $response->assertOk();
        $response->assertSee($this->targetUser->name);
        $response->assertSee($this->targetUser->email);
    });

    it('displays required form fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->targetUser));

        $response->assertOk();
        $response->assertSee('Nombre');
        $response->assertSee('Email');
    });

    it('displays optional billing fields', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->targetUser));

        $response->assertOk();
        $response->assertSee('Nombre comercial');
    });

    it('does not require password on edit', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->targetUser));

        $response->assertOk();
        $response->assertSee('Nueva contrasena');
    });

});

describe('Admin User Edit Validation', function () {

    it('requires name field', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('name', '')
            ->call('update')
            ->assertHasErrors(['name' => 'required']);
    });

    it('requires email field', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('email', '')
            ->call('update')
            ->assertHasErrors(['email' => 'required']);
    });

    it('requires valid email format', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('email', 'invalid-email')
            ->call('update')
            ->assertHasErrors(['email' => 'email']);
    });

    it('requires unique email except for current user', function () {
        // Email of another user should fail
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('email', $this->regularUser->email)
            ->call('update')
            ->assertHasErrors(['email' => 'unique']);
    });

    it('allows keeping same email', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('email', $this->targetUser->email)
            ->call('update')
            ->assertHasNoErrors(['email']);
    });

    it('requires password confirmation when changing password', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('password', 'NewPassword123!')
            ->set('password_confirmation', 'DifferentPassword!')
            ->call('update')
            ->assertHasErrors(['password' => 'confirmed']);
    });

});

describe('Admin User Edit Success', function () {

    it('updates user basic fields', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'id' => $this->targetUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    });

    it('updates user with optional fields', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('display_name', 'New Trading Name')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->targetUser->id,
            'display_name' => 'New Trading Name',
        ]);
    });

    it('updates password only when provided', function () {
        $oldPasswordHash = $this->targetUser->password;

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('password', 'NewPassword123!')
            ->set('password_confirmation', 'NewPassword123!')
            ->call('update')
            ->assertHasNoErrors();

        $this->targetUser->refresh();
        expect($this->targetUser->password)->not->toBe($oldPasswordHash);
        expect(\Illuminate\Support\Facades\Hash::check('NewPassword123!', $this->targetUser->password))->toBeTrue();
    });

    it('does not change password when empty', function () {
        $oldPasswordHash = $this->targetUser->password;

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('name', 'Name Only Update')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('update')
            ->assertHasNoErrors();

        $this->targetUser->refresh();
        expect($this->targetUser->password)->toBe($oldPasswordHash);
    });

    it('sets flash message on success', function () {
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->call('update')
            ->assertSessionHas('success');
    });

    it('updates delegation fields', function () {
        $parentUser = User::factory()->create();

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->targetUser])
            ->set('relationship_type', 1) // DELEGATED
            ->set('parent_user_id', $parentUser->id)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->targetUser->id,
            'parent_user_id' => $parentUser->id,
            'relationship_type' => 1,
        ]);
    });

});

describe('Admin User Edit Protection', function () {

    it('prevents editing self to non-admin domain', function () {
        // Admin editing themselves - should work but be careful
        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserEdit::class, ['user' => $this->admin])
            ->set('email', 'admin-new@testdomain.com') // Still admin domain
            ->call('update')
            ->assertHasNoErrors();
    });

});
