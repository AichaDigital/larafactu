<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold font-mono">{{ $invoiceSeries->prefix }}</h1>
            <p class="text-base-content/60 text-sm mt-1">{{ $invoiceSeries->description ?? 'Serie de facturacion' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.invoice-series') }}" class="btn btn-ghost gap-2" wire:navigate>
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                </svg>
                Volver
            </a>
            <a href="{{ route('admin.invoice-series.edit', $invoiceSeries) }}" class="btn btn-primary gap-2" wire:navigate>
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>
                </svg>
                Editar
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if($invoiceSeries->is_active)
            <span class="badge badge-success">Activa</span>
        @else
            <span class="badge badge-ghost">Inactiva</span>
        @endif
        @switch($invoiceSeries->serie->value)
            @case(0)
                <span class="badge badge-ghost">Proforma</span>
                @break
            @case(1)
                <span class="badge badge-primary">Factura</span>
                @break
            @case(2)
                <span class="badge badge-secondary">Simplificada</span>
                @break
            @case(3)
                <span class="badge badge-warning">Rectificativa</span>
                @break
        @endswitch
        @if($invoiceSeries->reset_annually)
            <span class="badge badge-info">Reinicio Anual</span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Series Identity -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Identificacion</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Prefijo</dt>
                    <dd class="font-mono font-bold text-lg">{{ $invoiceSeries->prefix }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Tipo de Serie</dt>
                    <dd>{{ $serieLabel }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Descripcion</dt>
                    <dd>{{ $invoiceSeries->description ?? '-' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Fiscal Year -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Ano Fiscal</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Ano</dt>
                    <dd class="font-bold text-lg">{{ $invoiceSeries->fiscal_year }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Periodo</dt>
                    <dd>{{ $invoiceSeries->fiscal_year_start->format('d/m/Y') }} - {{ $invoiceSeries->fiscal_year_end->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Reinicio anual</dt>
                    <dd>
                        @if($invoiceSeries->reset_annually)
                            <span class="badge badge-success badge-sm">Si</span>
                        @else
                            <span class="badge badge-ghost badge-sm">No</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Numbering -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Numeracion</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Numero inicial</dt>
                    <dd class="font-mono">{{ $invoiceSeries->start_number }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Ultimo numero</dt>
                    <dd class="font-mono text-lg">{{ $invoiceSeries->last_number }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Siguiente numero</dt>
                    <dd class="font-mono font-bold text-primary">{{ $invoiceSeries->getNextNumber() }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Facturas emitidas</dt>
                    <dd>{{ max(0, $invoiceSeries->last_number - $invoiceSeries->start_number + 1) }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <!-- Format -->
        <x-ui.card>
            <h2 class="font-semibold text-lg mb-4">Formato</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-base-content/60 text-sm">Plantilla</dt>
                    <dd class="font-mono bg-base-200 p-2 rounded text-sm">{{ $invoiceSeries->number_format }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60 text-sm">Ejemplo</dt>
                    <dd class="font-mono text-lg">
                        @php
                            $example = str_replace(
                                ['{{prefix}}', '{{year}}', '{{number}}'],
                                [$invoiceSeries->prefix, $invoiceSeries->fiscal_year, str_pad((string)$invoiceSeries->getNextNumber(), 5, '0', STR_PAD_LEFT)],
                                $invoiceSeries->number_format
                            );
                        @endphp
                        {{ $example }}
                    </dd>
                </div>
            </dl>
        </x-ui.card>
    </div>

    <!-- Metadata -->
    <x-ui.card class="mt-6">
        <h2 class="font-semibold text-lg mb-4">Informacion</h2>
        <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-base-content/60">Creado</dt>
                <dd>{{ $invoiceSeries->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-base-content/60">Actualizado</dt>
                <dd>{{ $invoiceSeries->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-base-content/60">Ultimo uso</dt>
                <dd>{{ $invoiceSeries->last_used_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-base-content/60">ID</dt>
                <dd class="font-mono text-xs">{{ $invoiceSeries->id }}</dd>
            </div>
        </dl>
    </x-ui.card>
</div>
