<x-layouts.app>
    <x-slot name="title">Usuarios</x-slot>

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
            <button class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" x2="19" y1="8" y2="14"/>
                    <line x1="22" x2="16" y1="11" y2="11"/>
                </svg>
                Nuevo usuario
            </button>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex flex-wrap gap-3">
                    <div class="join">
                        <input type="text" placeholder="Buscar por nombre o email..." class="input input-bordered join-item w-64" />
                        <button class="btn btn-primary join-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>
                        </button>
                    </div>
                    <select class="select select-bordered">
                        <option selected>Todos los tipos</option>
                        <option>Administrador</option>
                        <option>Usuario</option>
                        <option>Delegado</option>
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
                                <th class="text-base-content/60 font-medium">Usuario</th>
                                <th class="text-base-content/60 font-medium">Email</th>
                                <th class="text-base-content/60 font-medium">Tipo</th>
                                <th class="text-base-content/60 font-medium">Creado</th>
                                <th class="text-base-content/60 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3 text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 21a8 8 0 0 0-16 0"/>
                                            <circle cx="10" cy="8" r="5"/>
                                            <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-base-content">Lista de usuarios</p>
                                            <p class="text-sm">Pendiente de implementar con Livewire</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
