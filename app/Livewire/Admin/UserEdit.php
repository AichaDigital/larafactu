<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\LegalEntityType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin User Edit component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Editar Usuario - Admin')]
class UserEdit extends Component
{
    public User $user;

    // Authentication fields
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Billing fields
    public string $display_name = '';

    public ?string $legal_entity_type_code = null;

    // Delegation fields
    public ?string $parent_user_id = null;

    public int $relationship_type = 0;

    /**
     * Mount the component.
     */
    public function mount(User $user): void
    {
        Gate::authorize('manage-users');

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->display_name = $user->display_name ?? '';
        $this->legal_entity_type_code = $user->legal_entity_type_code;
        $this->parent_user_id = $user->parent_user_id;
        $this->relationship_type = $user->relationship_type?->value ?? 0;
    }

    /**
     * Get available legal entity types.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LegalEntityType>
     */
    #[Computed]
    public function legalEntityTypes()
    {
        return LegalEntityType::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get available parent users (for delegation).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    #[Computed]
    public function parentUsers()
    {
        return User::whereNull('parent_user_id')
            ->where('id', '!=', $this->user->id) // Exclude self
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Get relationship type options.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function relationshipTypes(): array
    {
        return UserRelationshipType::toArray();
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'legal_entity_type_code' => ['nullable', 'string', 'exists:legal_entity_types,code'],
            'parent_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'relationship_type' => ['required', 'integer', 'in:0,1'],
        ];

        // Password only required when provided
        if ($this->password) {
            $rules['password'] = ['confirmed', Password::defaults()];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser valido.',
            'email.unique' => 'Este email ya esta registrado.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'parent_user_id.exists' => 'El usuario padre seleccionado no existe.',
        ];
    }

    /**
     * Handle relationship type change.
     */
    public function updatedRelationshipType(): void
    {
        // If set to DIRECT, clear parent user
        if ($this->relationship_type === UserRelationshipType::DIRECT->value) {
            $this->parent_user_id = null;
        }
    }

    /**
     * Update the user.
     */
    public function update(): void
    {
        Gate::authorize('manage-users');

        $validated = $this->validate();

        // Ensure parent_user_id is null for DIRECT users
        if ($validated['relationship_type'] === UserRelationshipType::DIRECT->value) {
            $validated['parent_user_id'] = null;
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'display_name' => $validated['display_name'] ?: null,
            'legal_entity_type_code' => $validated['legal_entity_type_code'],
            'parent_user_id' => $validated['parent_user_id'],
            'relationship_type' => $validated['relationship_type'],
        ];

        // Only update password if provided
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        session()->flash('success', "Usuario {$this->user->name} actualizado correctamente.");

        $this->redirect(route('admin.users'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.user-edit');
    }
}
