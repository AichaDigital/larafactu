<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Nueva Serie de Facturacion</h1>
            <p class="text-base-content/60 text-sm mt-1">Crear nueva serie de numeracion de facturas</p>
        </div>
        <a href="{{ route('admin.invoice-series') }}" class="btn btn-ghost gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
            </svg>
            Volver
        </a>
    </div>

    <!-- Form -->
    <form wire:submit="create" class="space-y-6">
        <!-- Series Identity -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Identificacion de Serie</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Prefix -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Prefijo *</legend>
                    <input
                        type="text"
                        wire:model="prefix"
                        class="input w-full font-mono @error('prefix') input-error @enderror"
                        placeholder="F"
                        maxlength="10"
                    />
                    @error('prefix')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-base-content/60 text-xs mt-1">Ej: F, R, T, PRO</p>
                </fieldset>

                <!-- Serie Type -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Tipo de Serie *</legend>
                    <select
                        wire:model="serie"
                        class="select w-full @error('serie') select-error @enderror"
                    >
                        <option value="">Seleccionar...</option>
                        @foreach($this->serieTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('serie')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Fiscal Year -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Ano Fiscal *</legend>
                    <input
                        type="number"
                        wire:model="fiscal_year"
                        class="input w-full @error('fiscal_year') input-error @enderror"
                        min="2020"
                        max="2100"
                    />
                    @error('fiscal_year')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Description -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Descripcion</legend>
                    <input
                        type="text"
                        wire:model="description"
                        class="input w-full @error('description') input-error @enderror"
                        placeholder="Descripcion opcional de la serie"
                    />
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Numbering Configuration -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Configuracion de Numeracion</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Start Number -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Numero inicial *</legend>
                    <input
                        type="number"
                        wire:model="start_number"
                        class="input w-full @error('start_number') input-error @enderror"
                        min="1"
                    />
                    @error('start_number')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-base-content/60 text-xs mt-1">La primera factura tendra este numero</p>
                </fieldset>

                <!-- Number Format -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Formato de numero *</legend>
                    <input
                        type="text"
                        wire:model="number_format"
                        class="input w-full font-mono @error('number_format') input-error @enderror"
                        placeholder="{prefix}{year}-{number}"
                    />
                    @error('number_format')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-base-content/60 text-xs mt-1">Variables: @verbatim{{prefix}}, {{year}}, {{number}}@endverbatim</p>
                </fieldset>

                <!-- Reset Annually -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Reiniciar anualmente</legend>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="reset_annually"
                            class="toggle toggle-primary"
                        />
                        <span class="text-sm">Reiniciar numeracion cada ano fiscal</span>
                    </label>
                    <p class="text-base-content/60 text-xs mt-1">Recomendado para facturas fiscales</p>
                </fieldset>

                <!-- Is Active -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Estado</legend>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="is_active"
                            class="toggle toggle-success"
                        />
                        <span class="text-sm">Serie activa</span>
                    </label>
                    <p class="text-base-content/60 text-xs mt-1">Solo las series activas pueden emitir facturas</p>
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Preview -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Vista Previa</h2>
            <div class="bg-base-200 rounded-lg p-4">
                <p class="text-base-content/60 text-sm mb-2">Ejemplo de numero de factura:</p>
                <p class="font-mono text-lg">
                    @php
                        $preview = str_replace(
                            ['@{{prefix}}', '@{{year}}', '@{{number}}', '{{prefix}}', '{{year}}', '{{number}}'],
                            [$prefix ?: 'F', $fiscal_year ?: date('Y'), str_pad((string)$start_number, 5, '0', STR_PAD_LEFT), $prefix ?: 'F', $fiscal_year ?: date('Y'), str_pad((string)$start_number, 5, '0', STR_PAD_LEFT)],
                            $number_format
                        );
                    @endphp
                    {{ $preview }}
                </p>
            </div>
        </x-ui.card>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.invoice-series') }}" class="btn btn-ghost" wire:navigate>
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary gap-2">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Crear Serie
            </button>
        </div>
    </form>
</div>
