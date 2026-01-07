<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold font-mono">{{ $invoice->fiscal_number }}</h1>
                <span class="badge {{ $this->getStatusColor() }}">{{ $this->getStatusLabel() }}</span>
            </div>
            <p class="text-base-content/60 text-sm mt-1">{{ $this->getSerieLabel() }} - {{ $invoice->invoice_date->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-ghost gap-2" wire:navigate>
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                </svg>
                Volver
            </a>
            @if($this->canEdit())
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary gap-2" wire:navigate>
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                    </svg>
                    Editar
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Info -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Datos del Cliente</h2>
                @if($invoice->billableUser)
                    <div class="flex items-start gap-4">
                        <x-avatar :user="$invoice->billableUser" size="lg" />
                        <div class="flex-1">
                            <p class="font-medium">{{ $customerProfile?->fiscal_name ?? $invoice->billableUser->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $invoice->billableUser->email }}</p>
                            @if($customerProfile)
                                @if($customerProfile->tax_id)
                                    <p class="text-sm font-mono mt-2">NIF/CIF: {{ $customerProfile->tax_id }}</p>
                                @endif
                                @if($customerProfile->address || $customerProfile->city)
                                    <p class="text-sm text-base-content/60 mt-1">
                                        {{ $customerProfile->address }}
                                        @if($customerProfile->zip_code || $customerProfile->city)
                                            <br>{{ $customerProfile->zip_code }} {{ $customerProfile->city }}
                                        @endif
                                        @if($customerProfile->state)
                                            <br>{{ $customerProfile->state }}
                                        @endif
                                        @if($customerProfile->country_code && $customerProfile->country_code !== 'ES')
                                            <br>{{ $customerProfile->country_code }}
                                        @endif
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-base-content/60">Sin cliente asignado</p>
                @endif
            </x-ui.card>

            <!-- Invoice Items -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Lineas de Factura</h2>
                @if($invoice->items && $invoice->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-right">Precio</th>
                                    <th class="text-right">IVA</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $item->description }}</div>
                                            @if($item->notes)
                                                <div class="text-xs text-base-content/60">{{ $item->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right font-mono">{{ number_format($item->unit_price / 100, 2, ',', '.') }} &euro;</td>
                                        <td class="text-right">
                                            <span class="badge badge-ghost badge-sm">{{ $item->tax_rate ?? 21 }}%</span>
                                        </td>
                                        <td class="text-right font-mono font-medium">{{ number_format($item->total_amount / 100, 2, ',', '.') }} &euro;</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state
                        title="Sin lineas"
                        description="Esta factura no tiene lineas"
                    />
                @endif
            </x-ui.card>

            <!-- Notes -->
            @if($invoice->notes)
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Notas</h2>
                    <p class="text-base-content/70 whitespace-pre-wrap">{{ $invoice->notes }}</p>
                </x-ui.card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Totals -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Resumen</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Base imponible</dt>
                        <dd class="font-mono">{{ number_format($invoice->taxable_amount / 100, 2, ',', '.') }} &euro;</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">IVA</dt>
                        <dd class="font-mono">{{ number_format($invoice->total_tax_amount / 100, 2, ',', '.') }} &euro;</dd>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="flex justify-between text-lg font-bold">
                        <dt>Total</dt>
                        <dd class="font-mono">{{ number_format($invoice->total_amount / 100, 2, ',', '.') }} &euro;</dd>
                    </div>
                </dl>
            </x-ui.card>

            <!-- Invoice Details -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Detalles</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Serie</dt>
                        <dd>{{ $this->getSerieLabel() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Numero</dt>
                        <dd class="font-mono">{{ $invoice->series_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Ano fiscal</dt>
                        <dd>{{ $invoice->fiscal_year }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Fecha factura</dt>
                        <dd>{{ $invoice->invoice_date->format('d/m/Y') }}</dd>
                    </div>
                    @if($invoice->due_date)
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Vencimiento</dt>
                            <dd>{{ $invoice->due_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($invoice->service_date)
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Fecha servicio</dt>
                            <dd>{{ $invoice->service_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($invoice->paid_at)
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Pagada</dt>
                            <dd class="text-success">{{ $invoice->paid_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            <!-- Fiscal Info -->
            @if($invoice->isFiscallyVerified())
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Verificacion Fiscal</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-base-content/60">ID Verificacion</dt>
                            <dd class="font-mono text-xs break-all">{{ $invoice->fiscal_verification_id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Verificada</dt>
                            <dd>{{ $invoice->fiscal_verified_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            @endif

            <!-- Actions -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Acciones</h2>
                <div class="space-y-2">
                    <a href="{{ route('invoices.pdf', ['invoice' => $invoice, 'download' => 1]) }}" class="btn btn-outline btn-sm w-full gap-2" target="_blank">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
                        </svg>
                        Descargar PDF
                    </a>
                    <button class="btn btn-outline btn-sm w-full gap-2" disabled>
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        Enviar por email
                    </button>
                    @if($invoice->status === 0)
                        <button class="btn btn-outline btn-sm w-full gap-2 btn-success" disabled>
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Finalizar factura
                        </button>
                    @endif
                </div>
                <p class="text-xs text-base-content/50 mt-3">Funcionalidades en desarrollo</p>
            </x-ui.card>
        </div>
    </div>
</div>
