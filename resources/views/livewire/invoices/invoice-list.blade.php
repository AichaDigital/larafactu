<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Facturas</h1>
            <p class="text-base-content/60 text-sm mt-1">Gestiona tus facturas y su estado</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary gap-2" wire:navigate>
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/>
            </svg>
            Nueva factura
        </a>
    </div>

    <!-- Flash messages -->
    @if(session('success'))
        <x-ui.alert type="success" class="mb-4" dismissible>
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="error" class="mb-4" dismissible>
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label class="input w-full">
                    <svg class="size-5 text-base-content/60" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por numero o cliente..."
                        class="grow"
                    />
                </label>
            </div>

            <!-- Year filter -->
            <div class="w-full lg:w-32">
                <select wire:model.live="year" class="select w-full">
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Serie filter -->
            <div class="w-full lg:w-40">
                <select wire:model.live="serie" class="select w-full">
                    <option value="">Todas las series</option>
                    <option value="0">Proforma</option>
                    <option value="1">Factura</option>
                    <option value="2">Simplificada</option>
                    <option value="3">Rectificativa</option>
                </select>
            </div>

            <!-- Status filter -->
            <div class="w-full lg:w-40">
                <select wire:model.live="status" class="select w-full">
                    <option value="">Todos los estados</option>
                    <option value="0">Borrador</option>
                    <option value="1">Enviada</option>
                    <option value="2">Pagada</option>
                    <option value="3">Vencida</option>
                    <option value="4">Anulada</option>
                    <option value="5">Pendiente</option>
                    <option value="6">Convertida</option>
                </select>
            </div>
        </div>
    </x-ui.card>

    <!-- Results -->
    <x-ui.card>
        @if($invoices->isEmpty())
            <x-ui.empty-state
                title="No hay facturas"
                description="Crea tu primera factura para empezar"
                action-label="Nueva factura"
                action-href="{{ route('invoices.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Serie</th>
                            <th>Estado</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}">
                                <td>
                                    <span class="font-mono font-medium">{{ $invoice->fiscal_number }}</span>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    @if($invoice->billableUser)
                                        <div class="flex items-center gap-2">
                                            <div class="avatar placeholder">
                                                <div class="bg-neutral text-neutral-content w-8 rounded-full">
                                                    <span class="text-xs">{{ strtoupper(substr($invoice->billableUser->name, 0, 2)) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-sm">{{ $invoice->billableUser->name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $serieLabel = match($invoice->serie) {
                                            0 => 'Proforma',
                                            1 => 'Factura',
                                            2 => 'Simplificada',
                                            3 => 'Rectificativa',
                                            default => 'Desconocido',
                                        };
                                    @endphp
                                    <span class="badge badge-outline badge-sm">
                                        {{ $serieLabel }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusLabel = match($invoice->status) {
                                            0 => 'Borrador',
                                            1 => 'Enviada',
                                            2 => 'Pagada',
                                            3 => 'Vencida',
                                            4 => 'Anulada',
                                            5 => 'Pendiente',
                                            6 => 'Convertida',
                                            default => 'Desconocido',
                                        };
                                        $statusColor = match($invoice->status) {
                                            0 => 'badge-ghost',      // DRAFT
                                            1 => 'badge-info',       // SENT
                                            2 => 'badge-success',    // PAID
                                            3 => 'badge-error',      // OVERDUE
                                            4 => 'badge-neutral',    // CANCELLED
                                            5 => 'badge-warning',    // PENDING
                                            6 => 'badge-secondary',  // CONVERTED
                                            default => 'badge-ghost',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusColor }} badge-sm">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="font-mono font-medium">
                                        {{ number_format($invoice->total_amount / 100, 2, ',', '.') }} &euro;
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a
                                            href="{{ route('invoices.show', $invoice) }}"
                                            class="btn btn-ghost btn-sm btn-square"
                                            title="Ver"
                                            wire:navigate
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        @if($invoice->status === 0)
                                            <a
                                                href="{{ route('invoices.edit', $invoice) }}"
                                                class="btn btn-ghost btn-sm btn-square"
                                                title="Editar"
                                                wire:navigate
                                            >
                                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="mt-4 border-t border-base-200 pt-4">
                    {{ $invoices->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
