<x-layouts.app>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-semibold text-base-content">Dashboard</h1>
            <p class="text-sm text-base-content/60">Bienvenido, {{ auth()->user()->name }}</p>
        </div>

        {{-- Stats using DaisyUI stats component --}}
        <div class="stats stats-vertical lg:stats-horizontal shadow w-full">
            {{-- Facturas este mes --}}
            <div class="stat">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                        <path d="M10 12h4"/>
                        <path d="M10 16h4"/>
                        <path d="M10 8h1"/>
                    </svg>
                </div>
                <div class="stat-title">Facturas este mes</div>
                <div class="stat-value text-primary">0</div>
                <div class="stat-desc">{{ now()->translatedFormat('F Y') }}</div>
            </div>

            {{-- Facturado --}}
            <div class="stat">
                <div class="stat-figure text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
                        <path d="M12 18V6"/>
                    </svg>
                </div>
                <div class="stat-title">Facturado</div>
                <div class="stat-value text-success">0,00 EUR</div>
                <div class="stat-desc">Este mes</div>
            </div>

            {{-- Clientes --}}
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="stat-title">Clientes</div>
                <div class="stat-value text-secondary">0</div>
                <div class="stat-desc">Activos</div>
            </div>

            {{-- Pendientes --}}
            <div class="stat">
                <div class="stat-figure text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="stat-title">Pendientes</div>
                <div class="stat-value text-warning">0</div>
                <div class="stat-desc">Facturas por cobrar</div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Acciones rapidas</h2>
                <div class="card-actions">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="M12 5v14"/>
                        </svg>
                        Nueva factura
                    </a>
                    <a href="{{ route('customers.create') }}" class="btn btn-outline btn-secondary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" x2="19" y1="8" y2="14"/>
                            <line x1="22" x2="16" y1="11" y2="11"/>
                        </svg>
                        Nuevo cliente
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent invoices --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">Ultimas facturas</h2>
                    <a href="{{ route('invoices.index') }}" class="btn btn-ghost btn-sm">
                        Ver todas
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Numero</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-3 text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                        </svg>
                                        <p>No hay facturas recientes</p>
                                        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                                            Crear primera factura
                                        </a>
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
