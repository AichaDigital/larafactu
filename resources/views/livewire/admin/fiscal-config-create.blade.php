<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Nueva Configuracion Fiscal</h1>
            <p class="text-base-content/60 text-sm mt-1">Crear nueva configuracion fiscal de la empresa</p>
        </div>
        <a href="{{ route('admin.fiscal-configs') }}" class="btn btn-ghost gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
            </svg>
            Volver
        </a>
    </div>

    <!-- Form -->
    <form wire:submit="create" class="space-y-6">
        <!-- Business Identity Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Identidad Fiscal</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Business Name -->
                <fieldset class="fieldset md:col-span-2">
                    <legend class="fieldset-legend">Razon Social *</legend>
                    <input
                        type="text"
                        wire:model="business_name"
                        class="input w-full @error('business_name') input-error @enderror"
                        placeholder="Nombre legal de la empresa"
                    />
                    @error('business_name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Tax ID -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">NIF/CIF *</legend>
                    <input
                        type="text"
                        wire:model="tax_id"
                        class="input w-full @error('tax_id') input-error @enderror"
                        placeholder="B12345678"
                    />
                    @error('tax_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Legal Entity Type -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Tipo de Entidad *</legend>
                    <select
                        wire:model="legal_entity_type"
                        class="select w-full @error('legal_entity_type') select-error @enderror"
                    >
                        <option value="">Seleccionar...</option>
                        <option value="SL">Sociedad Limitada (S.L.)</option>
                        <option value="SA">Sociedad Anonima (S.A.)</option>
                        <option value="SLU">Sociedad Limitada Unipersonal (S.L.U.)</option>
                        <option value="SAU">Sociedad Anonima Unipersonal (S.A.U.)</option>
                        <option value="AUTONOMO">Autonomo</option>
                        <option value="CB">Comunidad de Bienes (C.B.)</option>
                        <option value="SC">Sociedad Civil (S.C.)</option>
                        <option value="COOP">Cooperativa</option>
                    </select>
                    @error('legal_entity_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Address Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Direccion Fiscal</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Address -->
                <fieldset class="fieldset md:col-span-2">
                    <legend class="fieldset-legend">Direccion *</legend>
                    <input
                        type="text"
                        wire:model="address"
                        class="input w-full @error('address') input-error @enderror"
                        placeholder="Calle, numero, piso..."
                    />
                    @error('address')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- City -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Ciudad *</legend>
                    <input
                        type="text"
                        wire:model="city"
                        class="input w-full @error('city') input-error @enderror"
                        placeholder="Madrid"
                    />
                    @error('city')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- State -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Provincia</legend>
                    <input
                        type="text"
                        wire:model="state"
                        class="input w-full @error('state') input-error @enderror"
                        placeholder="Madrid"
                    />
                    @error('state')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Zip Code -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Codigo Postal *</legend>
                    <input
                        type="text"
                        wire:model="zip_code"
                        class="input w-full @error('zip_code') input-error @enderror"
                        placeholder="28001"
                    />
                    @error('zip_code')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Country Code -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Pais *</legend>
                    <select
                        wire:model="country_code"
                        class="select w-full @error('country_code') select-error @enderror"
                    >
                        @foreach($countryCodes as $code)
                            <option value="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                    @error('country_code')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Fiscal Options Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Opciones Fiscales</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Currency -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Moneda *</legend>
                    <select
                        wire:model="currency"
                        class="select w-full @error('currency') select-error @enderror"
                    >
                        @foreach($currencies as $curr)
                            <option value="{{ $curr }}">{{ $curr }}</option>
                        @endforeach
                    </select>
                    @error('currency')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Fiscal Year Start -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Inicio ano fiscal</legend>
                    <input
                        type="text"
                        wire:model="fiscal_year_start"
                        class="input w-full @error('fiscal_year_start') input-error @enderror"
                        placeholder="01-01"
                    />
                    @error('fiscal_year_start')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- OSS -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Regimen OSS</legend>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="is_oss"
                            class="toggle toggle-primary"
                        />
                        <span class="text-sm">Registrado en OSS (One Stop Shop)</span>
                    </label>
                    <p class="text-base-content/60 text-xs mt-1">Para ventas B2C intracomunitarias</p>
                </fieldset>

                <!-- ROI -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Operador ROI</legend>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="is_roi"
                            class="toggle toggle-primary"
                        />
                        <span class="text-sm">Operador de Inversion del Sujeto Pasivo</span>
                    </label>
                    <p class="text-base-content/60 text-xs mt-1">Inversion del sujeto pasivo en operaciones B2B</p>
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Validity Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Vigencia</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Valid From -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Vigente desde *</legend>
                    <input
                        type="date"
                        wire:model="valid_from"
                        class="input w-full @error('valid_from') input-error @enderror"
                    />
                    @error('valid_from')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-base-content/60 text-xs mt-1">La configuracion anterior se cerrara el dia anterior</p>
                </fieldset>

                <!-- Notes -->
                <fieldset class="fieldset md:col-span-2">
                    <legend class="fieldset-legend">Notas</legend>
                    <textarea
                        wire:model="notes"
                        class="textarea w-full @error('notes') textarea-error @enderror"
                        rows="3"
                        placeholder="Notas adicionales sobre este cambio de configuracion..."
                    ></textarea>
                    @error('notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.fiscal-configs') }}" class="btn btn-ghost" wire:navigate>
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary gap-2">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Crear Configuracion
            </button>
        </div>
    </form>
</div>
