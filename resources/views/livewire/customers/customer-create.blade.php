<div>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('customers.index') }}" class="btn btn-ghost btn-sm btn-square" wire:navigate>
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Nuevo cliente</h1>
            <p class="text-base-content/60 text-sm mt-1">Registra un nuevo cliente con sus datos fiscales</p>
        </div>
    </div>

    <form wire:submit="save">
        <!-- User Data -->
        <x-ui.card class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Datos de Cuenta</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nombre *</legend>
                    <input
                        type="text"
                        wire:model.blur="name"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="Nombre del cliente"
                        required
                    />
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Email -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Email *</legend>
                    <label class="input w-full @error('email') input-error @enderror">
                        <svg class="size-5 text-base-content/60" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        <input
                            type="email"
                            wire:model="email"
                            class="grow"
                            placeholder="cliente@email.com"
                            required
                        />
                    </label>
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Tax Profile -->
        <x-ui.card class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Datos Fiscales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Fiscal Name -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nombre Fiscal *</legend>
                    <input
                        type="text"
                        wire:model="fiscalName"
                        class="input w-full @error('fiscalName') input-error @enderror"
                        placeholder="Razon social o nombre fiscal"
                        required
                    />
                    @error('fiscalName')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Tax ID -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">NIF/CIF/VAT</legend>
                    <input
                        type="text"
                        wire:model="taxId"
                        class="input w-full font-mono @error('taxId') input-error @enderror"
                        placeholder="12345678A"
                    />
                    @error('taxId')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Address -->
                <fieldset class="fieldset md:col-span-2">
                    <legend class="fieldset-legend">Direccion</legend>
                    <input
                        type="text"
                        wire:model="address"
                        class="input w-full @error('address') input-error @enderror"
                        placeholder="Calle, numero, piso..."
                    />
                    @error('address')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- City -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Ciudad</legend>
                    <input
                        type="text"
                        wire:model="city"
                        class="input w-full @error('city') input-error @enderror"
                        placeholder="Madrid"
                    />
                    @error('city')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
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
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- ZIP Code -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Codigo Postal</legend>
                    <input
                        type="text"
                        wire:model="zipCode"
                        class="input w-full font-mono @error('zipCode') input-error @enderror"
                        placeholder="28001"
                    />
                    @error('zipCode')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Country -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Pais *</legend>
                    <select wire:model="countryCode" class="select w-full @error('countryCode') select-error @enderror">
                        <option value="ES">Espana</option>
                        <option value="PT">Portugal</option>
                        <option value="FR">Francia</option>
                        <option value="DE">Alemania</option>
                        <option value="IT">Italia</option>
                        <option value="GB">Reino Unido</option>
                        <option value="US">Estados Unidos</option>
                    </select>
                    @error('countryCode')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>

            <!-- Checkboxes -->
            <div class="divider"></div>

            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="isCompany" class="checkbox checkbox-primary" />
                    <span>Es empresa</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="isEuVatRegistered" class="checkbox checkbox-primary" />
                    <span>Registrado en VIES (VAT intracomunitario)</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="isExemptVat" class="checkbox checkbox-primary" />
                    <span>Exento de IVA</span>
                </label>
            </div>

            <!-- Notes -->
            <fieldset class="fieldset mt-4">
                <legend class="fieldset-legend">Notas</legend>
                <textarea
                    wire:model="notes"
                    class="textarea w-full h-24 @error('notes') textarea-error @enderror"
                    placeholder="Notas adicionales sobre el cliente..."
                ></textarea>
                @error('notes')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </fieldset>
        </x-ui.card>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('customers.index') }}" class="btn btn-ghost" wire:navigate>
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary gap-2" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/>
                    </svg>
                </span>
                <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                Guardar cliente
            </button>
        </div>
    </form>
</div>
