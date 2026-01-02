<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserCustomerAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin User List component.
 *
 * Provides listing, filtering, and basic management of users.
 *
 * @see ADR-004 Authorization System
 * @see ADR-006 Consolidated State
 */
#[Layout('components.layouts.app')]
#[Title('Usuarios - Admin')]
class UserList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDir = 'desc';

    public ?string $selectedUserId = null;

    public bool $showDeleteModal = false;

    public bool $showDelegatesModal = false;

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when filter changes.
     */
    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Sort by column.
     */
    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    /**
     * Get filtered and paginated users.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<User>
     */
    #[Computed]
    public function users()
    {
        $query = User::query()
            ->withCount(['delegatedUsers', 'customerAccess', 'delegateAccess']);

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('display_name', 'like', "%{$this->search}%");
            });
        }

        // Type filter
        match ($this->filter) {
            'admin' => $query->whereRaw("(email LIKE '%@".config('app.admin_domains', 'impossible-domain.test')."')"),
            'with_delegates' => $query->has('delegatedUsers'),
            'is_delegate' => $query->whereNotNull('parent_user_id'),
            'direct' => $query->whereNull('parent_user_id'),
            default => null,
        };

        // Sort
        $query->orderBy($this->sortBy, $this->sortDir);

        return $query->paginate(15);
    }

    /**
     * Get selected user for modals.
     */
    #[Computed]
    public function selectedUser(): ?User
    {
        if (! $this->selectedUserId) {
            return null;
        }

        return User::with(['delegatedUsers', 'customerAccess.customer', 'delegateAccess.user'])
            ->find($this->selectedUserId);
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(string $userId): void
    {
        $this->selectedUserId = $userId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete user.
     */
    public function deleteUser(): void
    {
        if (! $this->selectedUserId) {
            return;
        }

        Gate::authorize('manage-users');

        $user = User::find($this->selectedUserId);
        if ($user) {
            // Don't allow deleting yourself
            if ($user->id === auth()->id()) {
                session()->flash('error', 'No puedes eliminar tu propia cuenta.');
                $this->showDeleteModal = false;

                return;
            }

            // Don't allow deleting other admins
            if ($user->isAdmin()) {
                session()->flash('error', 'No puedes eliminar a otro administrador.');
                $this->showDeleteModal = false;

                return;
            }

            $user->delete();
            session()->flash('success', "Usuario {$user->name} eliminado correctamente.");
        }

        $this->showDeleteModal = false;
        $this->selectedUserId = null;
    }

    /**
     * Show delegates modal for a user.
     */
    public function showDelegates(string $userId): void
    {
        $this->selectedUserId = $userId;
        $this->showDelegatesModal = true;
    }

    /**
     * Close modals.
     */
    public function closeModals(): void
    {
        $this->showDeleteModal = false;
        $this->showDelegatesModal = false;
        $this->selectedUserId = null;
    }

    /**
     * Revoke delegate access.
     */
    public function revokeAccess(int $accessId): void
    {
        $access = UserCustomerAccess::find($accessId);
        if ($access) {
            Gate::authorize('delete', $access);
            $access->delete();
            session()->flash('success', 'Acceso revocado correctamente.');
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.user-list');
    }
}
