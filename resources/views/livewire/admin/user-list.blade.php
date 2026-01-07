<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-circle btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Usuarios</h1>
                <p class="text-sm text-base-content/60">Gestion de usuarios del sistema</p>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-4">
            <div class="flex flex-wrap gap-3">
                <div class="join">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre o email..."
                        class="input input-bordered join-item w-64"
                    />
                    <button class="btn btn-primary join-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                    </button>
                </div>
                <select wire:model.live="filter" class="select select-bordered">
                    <option value="all">Todos los usuarios</option>
                    <option value="admin">Administradores</option>
                    <option value="direct">Usuarios directos</option>
                    <option value="is_delegate">Delegados</option>
                    <option value="with_delegates">Con delegados</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr class="border-base-300">
                            <th class="text-base-content/60 font-medium">
                                <button wire:click="sortBy('name')" class="flex items-center gap-1 hover:text-base-content">
                                    Usuario
                                    @if ($sortBy === 'name')
                                        <svg class="w-4 h-4 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="text-base-content/60 font-medium">
                                <button wire:click="sortBy('email')" class="flex items-center gap-1 hover:text-base-content">
                                    Email
                                    @if ($sortBy === 'email')
                                        <svg class="w-4 h-4 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="text-base-content/60 font-medium">Estado</th>
                            <th class="text-base-content/60 font-medium">Relaciones</th>
                            <th class="text-base-content/60 font-medium">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-base-content">
                                    Creado
                                    @if ($sortBy === 'created_at')
                                        <svg class="w-4 h-4 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="text-base-content/60 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->users as $user)
                            <tr class="hover:bg-base-200/50 border-base-300" wire:key="user-{{ $user->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-avatar :user="$user" size="md" />
                                        <div>
                                            <div class="font-medium">{{ $user->name }}</div>
                                            @if ($user->display_name)
                                                <div class="text-sm text-base-content/60">{{ $user->display_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-base-content/80">{{ $user->email }}</span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @if ($user->isAdmin())
                                            <span class="badge badge-primary badge-sm">Admin</span>
                                        @endif
                                        @if ($user->parent_user_id)
                                            <span class="badge badge-secondary badge-sm">Delegado</span>
                                        @endif
                                        @if ($user->email_verified_at)
                                            <span class="badge badge-success badge-sm badge-outline">Verificado</span>
                                        @else
                                            <span class="badge badge-warning badge-sm badge-outline">Pendiente</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @if ($user->delegated_users_count > 0)
                                            <span class="badge badge-info badge-sm badge-outline">
                                                {{ $user->delegated_users_count }} delegado(s)
                                            </span>
                                        @endif
                                        @if ($user->customer_access_count > 0)
                                            <button
                                                wire:click="showDelegates('{{ $user->id }}')"
                                                class="badge badge-accent badge-sm cursor-pointer hover:badge-accent"
                                            >
                                                Acceso a {{ $user->customer_access_count }} cuenta(s)
                                            </button>
                                        @endif
                                        @if ($user->delegate_access_count > 0)
                                            <button
                                                wire:click="showDelegates('{{ $user->id }}')"
                                                class="badge badge-warning badge-sm cursor-pointer hover:badge-warning"
                                            >
                                                {{ $user->delegate_access_count }} delegado(s) asignados
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm text-base-content/60">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/>
                                            </svg>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300">
                                            <li>
                                                <a href="{{ route('customers.edit', $user) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                                    </svg>
                                                    Editar
                                                </a>
                                            </li>
                                            @if ($user->customer_access_count > 0 || $user->delegate_access_count > 0)
                                                <li>
                                                    <button wire:click="showDelegates('{{ $user->id }}')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                                            <circle cx="9" cy="7" r="4"/>
                                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                                        </svg>
                                                        Ver delegaciones
                                                    </button>
                                                </li>
                                            @endif
                                            @if (!$user->isAdmin() && $user->id !== auth()->id())
                                                <li>
                                                    <form action="{{ route('admin.impersonate.start', $user) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="flex items-center gap-2 w-full">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                                <circle cx="12" cy="12" r="3"/>
                                                            </svg>
                                                            Impersonar
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <button
                                                        wire:click="confirmDelete('{{ $user->id }}')"
                                                        class="text-error"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3 text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 21a8 8 0 0 0-16 0"/>
                                            <circle cx="10" cy="8" r="5"/>
                                            <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-base-content">No se encontraron usuarios</p>
                                            <p class="text-sm">Prueba a cambiar los filtros de busqueda</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($this->users->hasPages())
                <div class="p-4 border-t border-base-300">
                    {{ $this->users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal && $this->selectedUser)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Confirmar eliminacion</h3>
                <p class="py-4">
                    ¿Estas seguro de que quieres eliminar al usuario
                    <strong>{{ $this->selectedUser->name }}</strong>?
                </p>
                <p class="text-sm text-base-content/60">
                    Esta accion eliminara tambien todos los accesos delegados asociados.
                </p>
                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cancelar</button>
                    <button wire:click="deleteUser" class="btn btn-error">Eliminar</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeModals"></div>
        </div>
    @endif

    {{-- Delegates Modal --}}
    @if ($showDelegatesModal && $this->selectedUser)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">
                    Delegaciones de {{ $this->selectedUser->name }}
                </h3>

                {{-- Access this user has to other customers --}}
                @if ($this->selectedUser->customerAccess->count() > 0)
                    <div class="mb-6">
                        <h4 class="font-medium text-sm text-base-content/70 mb-2">
                            Tiene acceso a estas cuentas:
                        </h4>
                        <div class="space-y-2">
                            @foreach ($this->selectedUser->customerAccess as $access)
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                    <div>
                                        <div class="font-medium">{{ $access->customer->name }}</div>
                                        <div class="text-sm text-base-content/60">
                                            {{ $access->access_level->label() }}
                                            @if ($access->expires_at)
                                                - Expira: {{ $access->expires_at->format('d/m/Y') }}
                                            @endif
                                        </div>
                                        <div class="flex gap-1 mt-1">
                                            @if ($access->can_view_invoices)
                                                <span class="badge badge-xs">Facturas</span>
                                            @endif
                                            @if ($access->can_view_services)
                                                <span class="badge badge-xs">Servicios</span>
                                            @endif
                                            @if ($access->can_manage_tickets)
                                                <span class="badge badge-xs">Tickets</span>
                                            @endif
                                            @if ($access->can_manage_delegates)
                                                <span class="badge badge-xs">Delegados</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button
                                        wire:click="revokeAccess({{ $access->id }})"
                                        class="btn btn-ghost btn-sm btn-circle text-error"
                                        title="Revocar acceso"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Delegates that have access to this user's account --}}
                @if ($this->selectedUser->delegateAccess->count() > 0)
                    <div>
                        <h4 class="font-medium text-sm text-base-content/70 mb-2">
                            Delegados con acceso a su cuenta:
                        </h4>
                        <div class="space-y-2">
                            @foreach ($this->selectedUser->delegateAccess as $access)
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                    <div>
                                        <div class="font-medium">{{ $access->user->name }}</div>
                                        <div class="text-sm text-base-content/60">
                                            {{ $access->access_level->label() }}
                                            @if ($access->expires_at)
                                                - Expira: {{ $access->expires_at->format('d/m/Y') }}
                                            @endif
                                        </div>
                                        <div class="flex gap-1 mt-1">
                                            @if ($access->can_view_invoices)
                                                <span class="badge badge-xs">Facturas</span>
                                            @endif
                                            @if ($access->can_view_services)
                                                <span class="badge badge-xs">Servicios</span>
                                            @endif
                                            @if ($access->can_manage_tickets)
                                                <span class="badge badge-xs">Tickets</span>
                                            @endif
                                            @if ($access->can_manage_delegates)
                                                <span class="badge badge-xs">Delegados</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button
                                        wire:click="revokeAccess({{ $access->id }})"
                                        class="btn btn-ghost btn-sm btn-circle text-error"
                                        title="Revocar acceso"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($this->selectedUser->customerAccess->count() === 0 && $this->selectedUser->delegateAccess->count() === 0)
                    <div class="text-center py-8 text-base-content/50">
                        <p>Este usuario no tiene delegaciones activas.</p>
                    </div>
                @endif

                <div class="modal-action">
                    <button wire:click="closeModals" class="btn">Cerrar</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeModals"></div>
        </div>
    @endif
</div>
