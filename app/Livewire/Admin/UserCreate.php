<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\LegalEntityType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin User Create component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Crear Usuario - Admin')]
class UserCreate extends Component
{
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

    public int $relationship_type = 0; // DIRECT by default

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('manage-users');
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'display_name' => ['nullable', 'string', 'max:255'],
            'legal_entity_type_code' => ['nullable', 'string', 'exists:legal_entity_types,code'],
            'parent_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'relationship_type' => ['required', 'integer', 'in:0,1'],
        ];
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
            'password.required' => 'La contrasena es obligatoria.',
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
     * Create the user.
     */
    public function create(): void
    {
        Gate::authorize('manage-users');

        $validated = $this->validate();

        // Ensure parent_user_id is null for DIRECT users
        if ($validated['relationship_type'] === UserRelationshipType::DIRECT->value) {
            $validated['parent_user_id'] = null;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'display_name' => $validated['display_name'] ?: null,
            'legal_entity_type_code' => $validated['legal_entity_type_code'],
            'parent_user_id' => $validated['parent_user_id'],
            'relationship_type' => $validated['relationship_type'],
        ]);

        session()->flash('success', "Usuario {$user->name} creado correctamente.");

        $this->redirect(route('admin.users'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.user-create');
    }
}
