<x-layouts.app>
    <x-slot name="title">Nueva factura</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('invoices.index') }}" class="btn btn-ghost btn-circle btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-base-content">Nueva factura</h1>
                    <p class="text-sm text-base-content/60">Crea una nueva factura para tus clientes</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-outline gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                        <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/>
                        <path d="M7 3v4a1 1 0 0 0 1 1h7"/>
                    </svg>
                    Guardar borrador
                </button>
                <button class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m22 2-7 20-4-9-9-4Z"/>
                        <path d="M22 2 11 13"/>
                    </svg>
                    Emitir factura
                </button>
            </div>
        </div>

        {{-- Form placeholder --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Left: Main form --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Customer selection --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-primary/10 text-primary rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold">Cliente</h2>
                                <p class="text-sm text-base-content/60">Selecciona o crea un nuevo cliente</p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 16v-4"/>
                                <path d="M12 8h.01"/>
                            </svg>
                            <span>Selector de cliente pendiente de implementar con Livewire</span>
                        </div>
                    </div>
                </div>

                {{-- Invoice items --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-secondary/10 text-secondary rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m7.5 4.27 9 5.15"/>
                                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                        <path d="m3.3 7 8.7 5 8.7-5"/>
                                        <path d="M12 22V12"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold">Lineas de factura</h2>
                                    <p class="text-sm text-base-content/60">Anade productos o servicios</p>
                                </div>
                            </div>
                            <button class="btn btn-outline btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="M12 5v14"/>
                                </svg>
                                Anadir linea
                            </button>
                        </div>

                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 16v-4"/>
                                <path d="M12 8h.01"/>
                            </svg>
                            <span>Editor de lineas pendiente de implementar con Livewire</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Summary --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Invoice details --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        <h3 class="font-semibold mb-4">Detalles</h3>
                        <div class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Numero de factura</span>
                                </label>
                                <input type="text" class="input input-bordered" placeholder="Auto" disabled />
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Fecha de emision</span>
                                </label>
                                <input type="date" class="input input-bordered" value="{{ date('Y-m-d') }}" />
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Fecha de vencimiento</span>
                                </label>
                                <input type="date" class="input input-bordered" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        <h3 class="font-semibold mb-4">Resumen</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">Subtotal</span>
                                <span>0,00 EUR</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">IVA (21%)</span>
                                <span>0,00 EUR</span>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total</span>
                                <span class="text-primary">0,00 EUR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
