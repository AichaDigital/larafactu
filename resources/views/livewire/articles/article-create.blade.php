<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Nuevo Articulo</h1>
            <p class="text-base-content/60 text-sm mt-1">Crea un nuevo producto o servicio</p>
        </div>
        <a href="{{ route('articles.index') }}" class="btn btn-ghost gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
            </svg>
            Cancelar
        </a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Informacion basica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Codigo</label>
                            <div class="join w-full">
                                <input
                                    type="text"
                                    wire:model="code"
                                    class="input join-item flex-1 font-mono uppercase @error('code') input-error @enderror"
                                    placeholder="ART001"
                                />
                                <button
                                    type="button"
                                    wire:click="generateCode"
                                    class="btn btn-ghost join-item"
                                    title="Generar codigo"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                                    </svg>
                                </button>
                            </div>
                            @error('code')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label">Tipo</label>
                            <select wire:model="itemType" class="select w-full">
                                <option value="0">Producto</option>
                                <option value="1">Servicio</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="label">Nombre</label>
                            <input
                                type="text"
                                wire:model="name"
                                class="input w-full @error('name') input-error @enderror"
                                placeholder="Nombre del articulo"
                            />
                            @error('name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="label">Descripcion</label>
                            <textarea
                                wire:model="description"
                                class="textarea textarea-bordered w-full h-24"
                                placeholder="Descripcion del articulo (opcional)"
                            ></textarea>
                        </div>

                        <div>
                            <label class="label">Categoria</label>
                            <input
                                type="text"
                                wire:model="category"
                                list="categories"
                                class="input w-full"
                                placeholder="Ej: Hosting, Dominios, Consultoria..."
                            />
                            <datalist id="categories">
                                @foreach($existingCategories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="label">Coste (centimos)</label>
                            <input
                                type="number"
                                wire:model="costPrice"
                                class="input w-full"
                                min="0"
                                step="1"
                                placeholder="0"
                            />
                            <p class="text-xs text-base-content/50 mt-1">Coste interno (1234 = 12,34&euro;)</p>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Pricing -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Precio de venta</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Precio base (centimos)</label>
                            <input
                                type="number"
                                wire:model="basePrice"
                                class="input w-full @error('basePrice') input-error @enderror"
                                min="0"
                                step="1"
                                placeholder="0"
                            />
                            <p class="text-xs text-base-content/50 mt-1">Precio en centimos (1234 = 12,34&euro;)</p>
                            @error('basePrice')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label">Tipo IVA %</label>
                            <select wire:model="taxRate" class="select w-full">
                                <option value="0">0% - Exento</option>
                                <option value="4">4% - Superreducido</option>
                                <option value="10">10% - Reducido</option>
                                <option value="21">21% - General</option>
                            </select>
                        </div>
                    </div>

                    @if($basePrice > 0)
                        <div class="mt-4 p-4 bg-base-200 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">Base:</span>
                                <span class="font-mono">{{ number_format($basePrice / 100, 2, ',', '.') }} &euro;</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">IVA ({{ $taxRate }}%):</span>
                                <span class="font-mono">{{ number_format(($basePrice * $taxRate / 100) / 100, 2, ',', '.') }} &euro;</span>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="flex justify-between font-medium">
                                <span>Total:</span>
                                <span class="font-mono">{{ number_format(($basePrice * (1 + $taxRate / 100)) / 100, 2, ',', '.') }} &euro;</span>
                            </div>
                        </div>
                    @endif
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Estado</h2>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="isActive"
                            class="toggle toggle-success"
                        />
                        <span>Articulo activo</span>
                    </label>
                    <p class="text-xs text-base-content/50 mt-2">
                        Solo los articulos activos pueden usarse en facturas
                    </p>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="M12 5v14"/>
                        </svg>
                        Crear articulo
                    </button>
                </x-ui.card>

                <!-- Help -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Ayuda</h2>
                    <div class="text-sm text-base-content/70 space-y-2">
                        <p><strong>Productos:</strong> Bienes fisicos (hosting, dominios, licencias).</p>
                        <p><strong>Servicios:</strong> Trabajo o asistencia (consultoria, mantenimiento).</p>
                        <p><strong>Precios:</strong> Se introducen en centimos. 1234 = 12,34&euro;</p>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
