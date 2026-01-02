<x-layouts.app>
    <x-slot name="title">Administracion</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold text-base-content">Panel de administracion</h1>
            <p class="text-sm text-base-content/60">Configuracion del sistema y gestion de usuarios</p>
        </div>

        {{-- Admin cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Users --}}
            <a href="{{ route('admin.users') }}" class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors group">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-primary/10 text-primary rounded-lg p-2.5 group-hover:bg-primary group-hover:text-primary-content transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 21a8 8 0 0 0-16 0"/>
                                <circle cx="10" cy="8" r="5"/>
                                <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold">Usuarios</h2>
                    <p class="text-sm text-base-content/60">Gestionar usuarios del sistema</p>
                </div>
            </a>

            {{-- Fiscal config --}}
            <div class="card bg-base-100 border border-base-300 opacity-60">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-secondary/10 text-secondary rounded-lg p-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                                <path d="M10 6h4"/>
                                <path d="M10 10h4"/>
                                <path d="M10 14h4"/>
                                <path d="M10 18h4"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold">Configuracion fiscal</h2>
                    <p class="text-sm text-base-content/60">Datos de la empresa emisora</p>
                    <span class="badge badge-ghost badge-sm mt-2">Proximamente</span>
                </div>
            </div>

            {{-- Invoice series --}}
            <div class="card bg-base-100 border border-base-300 opacity-60">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-accent/10 text-accent rounded-lg p-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                <path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/>
                                <path d="M12 11h4"/>
                                <path d="M12 16h4"/>
                                <path d="M8 11h.01"/>
                                <path d="M8 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold">Series de factura</h2>
                    <p class="text-sm text-base-content/60">Gestionar series y numeracion</p>
                    <span class="badge badge-ghost badge-sm mt-2">Proximamente</span>
                </div>
            </div>

            {{-- VeriFactu --}}
            <div class="card bg-base-100 border border-base-300 opacity-60">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="bg-warning/10 text-warning rounded-lg p-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                                <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                                <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold">VeriFactu</h2>
                    <p class="text-sm text-base-content/60">Configurar conexion con AEAT</p>
                    <span class="badge badge-ghost badge-sm mt-2">Proximamente</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
