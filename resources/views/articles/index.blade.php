<x-layouts.app>
    <x-slot name="title">Articulos</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Articulos</h1>
                <p class="text-sm text-base-content/60">Catalogo de productos y servicios</p>
            </div>
            <button class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="M12 5v14"/>
                </svg>
                Nuevo articulo
            </button>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex flex-wrap gap-3">
                    <div class="join">
                        <input type="text" placeholder="Buscar articulo..." class="input input-bordered join-item w-64" />
                        <button class="btn btn-primary join-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>
                        </button>
                    </div>
                    <select class="select select-bordered">
                        <option selected>Todos los tipos</option>
                        <option>Producto</option>
                        <option>Servicio</option>
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
                                <th class="text-base-content/60 font-medium">Nombre</th>
                                <th class="text-base-content/60 font-medium">Tipo</th>
                                <th class="text-base-content/60 font-medium text-right">Precio base</th>
                                <th class="text-base-content/60 font-medium">IVA</th>
                                <th class="text-base-content/60 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3 text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m7.5 4.27 9 5.15"/>
                                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                            <path d="m3.3 7 8.7 5 8.7-5"/>
                                            <path d="M12 22V12"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-base-content">No hay articulos</p>
                                            <p class="text-sm">Crea tu primer articulo para empezar</p>
                                        </div>
                                        <button class="btn btn-primary btn-sm mt-2 gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M5 12h14"/>
                                                <path d="M12 5v14"/>
                                            </svg>
                                            Nuevo articulo
                                        </button>
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
