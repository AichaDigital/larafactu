<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Configuracion Fiscal</h1>
            <p class="text-base-content/60 text-sm mt-1">Gestiona la configuracion fiscal de la empresa</p>
        </div>
        <a href="{{ route('admin.fiscal-configs.create') }}" class="btn btn-primary gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/>
            </svg>
            Nueva Configuracion
        </a>
    </div>

    <!-- Configs List -->
    <x-ui.card>
        @if($configs->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto size-12 text-base-content/30" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
                <p class="text-base-content/60 mt-2">No hay configuraciones fiscales</p>
                <a href="{{ route('admin.fiscal-configs.create') }}" class="btn btn-primary btn-sm mt-4" wire:navigate>
                    Crear primera configuracion
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Razon Social</th>
                            <th>NIF/CIF</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($configs as $config)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $config->business_name }}</div>
                                    <div class="text-sm text-base-content/60">{{ $config->legal_entity_type }}</div>
                                </td>
                                <td>{{ $config->tax_id }}</td>
                                <td>
                                    <div class="text-sm">{{ $config->validity_range }}</div>
                                </td>
                                <td>
                                    @if($config->isCurrentlyActive())
                                        <span class="badge badge-success badge-sm">Activa</span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">Historica</span>
                                    @endif
                                    @if($config->is_oss)
                                        <span class="badge badge-info badge-sm">OSS</span>
                                    @endif
                                    @if($config->is_roi)
                                        <span class="badge badge-warning badge-sm">ROI</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.fiscal-configs.show', $config) }}" class="btn btn-ghost btn-xs" wire:navigate title="Ver">
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.fiscal-configs.edit', $config) }}" class="btn btn-ghost btn-xs" wire:navigate title="Editar">
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</div>
