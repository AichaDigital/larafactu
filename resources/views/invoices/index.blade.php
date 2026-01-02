<x-layouts.app>
    <x-slot name="title">Facturas</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Facturas</h1>
                <p class="text-sm text-base-content/60">Gestiona tus facturas emitidas</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="M12 5v14"/>
                </svg>
                Nueva factura
            </a>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex flex-wrap gap-3">
                    <div class="join">
                        <input type="text" placeholder="Buscar por numero o cliente..." class="input input-bordered join-item w-64" />
                        <button class="btn btn-primary join-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>
                        </button>
                    </div>
                    <select class="select select-bordered">
                        <option selected>Todos los estados</option>
                        <option>Borrador</option>
                        <option>Emitida</option>
                        <option>Pagada</option>
                        <option>Anulada</option>
                    </select>
                    <input type="month" class="input input-bordered" />
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
                                <th class="text-base-content/60 font-medium">Numero</th>
                                <th class="text-base-content/60 font-medium">Cliente</th>
                                <th class="text-base-content/60 font-medium">Fecha</th>
                                <th class="text-base-content/60 font-medium text-right">Total</th>
                                <th class="text-base-content/60 font-medium">Estado</th>
                                <th class="text-base-content/60 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3 text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                            <path d="M10 12h4"/>
                                            <path d="M10 16h4"/>
                                            <path d="M10 8h1"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-base-content">No hay facturas</p>
                                            <p class="text-sm">Crea tu primera factura para empezar</p>
                                        </div>
                                        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm mt-2 gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M5 12h14"/>
                                                <path d="M12 5v14"/>
                                            </svg>
                                            Nueva factura
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
