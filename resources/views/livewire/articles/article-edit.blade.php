<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Editar Articulo</h1>
            <p class="text-base-content/60 text-sm mt-1">
                <span class="font-mono">{{ $article->code }}</span>
            </p>
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
                            <input
                                type="text"
                                wire:model="code"
                                class="input w-full font-mono uppercase @error('code') input-error @enderror"
                                placeholder="ART001"
                            />
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

                <!-- Info -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Informacion</h2>
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Creado</dt>
                            <dd>{{ $article->created_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Modificado</dt>
                            <dd>{{ $article->updated_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar cambios
                    </button>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
