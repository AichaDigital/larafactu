<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Editar Usuario</h1>
            <p class="text-base-content/60 text-sm mt-1">{{ $user->name }} ({{ $user->email }})</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-ghost gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
            </svg>
            Volver
        </a>
    </div>

    <!-- Form -->
    <form wire:submit="update" class="space-y-6">
        <!-- Authentication Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Datos de Acceso</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nombre *</legend>
                    <input
                        type="text"
                        wire:model="name"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="Nombre completo"
                    />
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Email -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Email *</legend>
                    <input
                        type="email"
                        wire:model="email"
                        class="input w-full @error('email') input-error @enderror"
                        placeholder="usuario@ejemplo.com"
                    />
                    @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Password -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nueva contrasena</legend>
                    <input
                        type="password"
                        wire:model="password"
                        class="input w-full @error('password') input-error @enderror"
                        placeholder="Dejar vacio para no cambiar"
                    />
                    @error('password')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Password Confirmation -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Confirmar contrasena</legend>
                    <input
                        type="password"
                        wire:model="password_confirmation"
                        class="input w-full"
                        placeholder="Repetir nueva contrasena"
                    />
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Billing Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Datos de Facturacion</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Display Name -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Nombre comercial</legend>
                    <input
                        type="text"
                        wire:model="display_name"
                        class="input w-full @error('display_name') input-error @enderror"
                        placeholder="Nombre para facturas (opcional)"
                    />
                    @error('display_name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Legal Entity Type -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Tipo de entidad</legend>
                    <select
                        wire:model="legal_entity_type_code"
                        class="select w-full @error('legal_entity_type_code') select-error @enderror"
                    >
                        <option value="">Seleccionar tipo...</option>
                        @foreach($this->legalEntityTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('legal_entity_type_code')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        </x-ui.card>

        <!-- Delegation Section -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Delegacion</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Relationship Type -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Tipo de relacion *</legend>
                    <select
                        wire:model.live="relationship_type"
                        class="select w-full @error('relationship_type') select-error @enderror"
                    >
                        @foreach($this->relationshipTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('relationship_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Parent User (only for DELEGATED) -->
                @if($relationship_type == 1)
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Usuario padre</legend>
                        <select
                            wire:model="parent_user_id"
                            class="select w-full @error('parent_user_id') select-error @enderror"
                        >
                            <option value="">Seleccionar usuario...</option>
                            @foreach($this->parentUsers as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->email }})</option>
                            @endforeach
                        </select>
                        @error('parent_user_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endif
            </div>
        </x-ui.card>

        <!-- User Info -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Informacion</h2>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-base-content/60">Creado</dt>
                    <dd>{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60">Actualizado</dt>
                    <dd>{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60">Email verificado</dt>
                    <dd>
                        @if($user->email_verified_at)
                            <span class="badge badge-success badge-sm">{{ $user->email_verified_at->format('d/m/Y') }}</span>
                        @else
                            <span class="badge badge-warning badge-sm">Pendiente</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.users') }}" class="btn btn-ghost" wire:navigate>
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary gap-2">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
