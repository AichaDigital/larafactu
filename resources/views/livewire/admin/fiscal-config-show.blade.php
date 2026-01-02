<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $fiscalConfig->business_name }}</h1>
            <p class="text-base-content/60 text-sm mt-1">{{ $fiscalConfig->tax_id }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.fiscal-configs') }}" class="btn btn-ghost gap-2" wire:navigate>
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                </svg>
                Volver
            </a>
            <a href="{{ route('admin.fiscal-configs.edit', $fiscalConfig) }}" class="btn btn-primary gap-2" wire:navigate>
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>
                </svg>
                Editar
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if($fiscalConfig->isCurrentlyActive())
            <span class="badge badge-success">Activa</span>
        @else
            <span class="badge badge-ghost">Historica</span>
        @endif
        @if($fiscalConfig->is_oss)
            <span class="badge badge-info">OSS</span>
        @endif
        @if($fiscalConfig->is_roi)
            <span class="badge badge-warning">ROI</span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Business Identity -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Identidad Fiscal</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Razon Social</dt>
                    <dd class="font-medium">{{ $fiscalConfig->business_name }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">NIF/CIF</dt>
                    <dd class="font-medium">{{ $fiscalConfig->tax_id }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Tipo de Entidad</dt>
                    <dd>{{ $fiscalConfig->legal_entity_type }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Address -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Direccion Fiscal</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Direccion</dt>
                    <dd>{{ $fiscalConfig->address }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Ciudad</dt>
                    <dd>{{ $fiscalConfig->city }}@if($fiscalConfig->state), {{ $fiscalConfig->state }}@endif</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Codigo Postal</dt>
                    <dd>{{ $fiscalConfig->zip_code }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Pais</dt>
                    <dd>{{ $fiscalConfig->country_code }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Fiscal Options -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Opciones Fiscales</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Moneda</dt>
                    <dd>{{ $fiscalConfig->currency }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Inicio ano fiscal</dt>
                    <dd>{{ $fiscalConfig->fiscal_year_start }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Regimen OSS</dt>
                    <dd>
                        @if($fiscalConfig->is_oss)
                            <span class="badge badge-success badge-sm">Activo</span>
                        @else
                            <span class="badge badge-ghost badge-sm">No</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Operador ROI</dt>
                    <dd>
                        @if($fiscalConfig->is_roi)
                            <span class="badge badge-success badge-sm">Activo</span>
                        @else
                            <span class="badge badge-ghost badge-sm">No</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Validity -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Vigencia</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Vigente desde</dt>
                    <dd>{{ $fiscalConfig->valid_from->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Vigente hasta</dt>
                    <dd>
                        @if($fiscalConfig->valid_until)
                            {{ $fiscalConfig->valid_until->format('d/m/Y') }}
                        @else
                            <span class="badge badge-success badge-sm">Actual</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Rango de vigencia</dt>
                    <dd>{{ $fiscalConfig->validity_range }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>

    <!-- Notes -->
    @if($fiscalConfig->notes)
        <x-ui.card class="mt-6">
            <h2 class="font-semibold text-lg mb-4">Notas</h2>
            <p class="whitespace-pre-wrap">{{ $fiscalConfig->notes }}</p>
        </x-ui.card>
    @endif

    <!-- Metadata -->
    <x-ui.card class="mt-6">
        <h2 class="font-semibold text-lg mb-4">Informacion</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-base-content/60">Creado</dt>
                <dd>{{ $fiscalConfig->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-base-content/60">Actualizado</dt>
                <dd>{{ $fiscalConfig->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-base-content/60">ID</dt>
                <dd class="font-mono text-xs">{{ $fiscalConfig->id }}</dd>
            </div>
        </dl>
    </x-ui.card>
</div>
