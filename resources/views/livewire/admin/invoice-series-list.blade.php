<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Series de Facturacion</h1>
            <p class="text-base-content/60 text-sm mt-1">Gestiona las series de numeracion de facturas</p>
        </div>
        <a href="{{ route('admin.invoice-series.create') }}" class="btn btn-primary gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/>
            </svg>
            Nueva Serie
        </a>
    </div>

    <!-- Series List -->
    <x-ui.card>
        @if($series->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto size-12 text-base-content/30" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <p class="text-base-content/60 mt-2">No hay series de facturacion</p>
                <a href="{{ route('admin.invoice-series.create') }}" class="btn btn-primary btn-sm mt-4" wire:navigate>
                    Crear primera serie
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Prefijo</th>
                            <th>Tipo</th>
                            <th>Ano Fiscal</th>
                            <th>Ultimo Num.</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($series as $item)
                            <tr>
                                <td>
                                    <div class="font-mono font-bold">{{ $item->prefix }}</div>
                                    @if($item->description)
                                        <div class="text-sm text-base-content/60">{{ Str::limit($item->description, 30) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @switch($item->serie->value)
                                        @case(0)
                                            <span class="badge badge-ghost badge-sm">Proforma</span>
                                            @break
                                        @case(1)
                                            <span class="badge badge-primary badge-sm">Factura</span>
                                            @break
                                        @case(2)
                                            <span class="badge badge-secondary badge-sm">Simplificada</span>
                                            @break
                                        @case(3)
                                            <span class="badge badge-warning badge-sm">Rectificativa</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $item->fiscal_year }}</td>
                                <td>
                                    <span class="font-mono">{{ $item->last_number }}</span>
                                    <span class="text-base-content/40 text-xs">/ {{ $item->start_number }}</span>
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge badge-success badge-sm">Activa</span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">Inactiva</span>
                                    @endif
                                    @if($item->reset_annually)
                                        <span class="badge badge-info badge-sm" title="Reinicio anual">RA</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.invoice-series.show', $item) }}" class="btn btn-ghost btn-xs" wire:navigate title="Ver">
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.invoice-series.edit', $item) }}" class="btn btn-ghost btn-xs" wire:navigate title="Editar">
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
