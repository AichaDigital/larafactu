<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Nueva Factura</h1>
            <p class="text-base-content/60 text-sm mt-1">Crea una nueva factura</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-ghost gap-2" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
            </svg>
            Cancelar
        </a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Selection -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Cliente</h2>

                    @if($selectedCustomer)
                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content w-10 rounded-full">
                                        <span class="text-sm">{{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $selectedCustomer->name }}</p>
                                    <p class="text-sm text-base-content/60">{{ $selectedCustomer->email }}</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="$set('customerId', '')"
                                class="btn btn-ghost btn-sm btn-square"
                            >
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                                </svg>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <label class="input w-full">
                                <svg class="size-5 text-base-content/60" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                </svg>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="customerSearch"
                                    placeholder="Buscar cliente por nombre o email..."
                                    class="grow"
                                />
                            </label>

                            @if($customerSearch && count($customers) > 0)
                                <ul class="menu bg-base-100 shadow-lg rounded-box absolute z-10 w-full mt-1 max-h-60 overflow-y-auto border border-base-300">
                                    @foreach($customers as $customer)
                                        <li>
                                            <button
                                                type="button"
                                                wire:click="selectCustomer('{{ $customer['id'] }}')"
                                                class="flex items-center gap-3"
                                            >
                                                <div class="avatar placeholder">
                                                    <div class="bg-neutral text-neutral-content w-8 rounded-full">
                                                        <span class="text-xs">{{ strtoupper(substr($customer['name'], 0, 2)) }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-left">
                                                    <p class="font-medium">{{ $customer['name'] }}</p>
                                                    <p class="text-xs text-base-content/60">{{ $customer['email'] }}</p>
                                                </div>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    @error('customerId')
                        <p class="text-error text-sm mt-2">{{ $message }}</p>
                    @enderror
                </x-ui.card>

                <!-- Invoice Items -->
                <x-ui.card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-lg">Lineas</h2>
                        <button type="button" wire:click="addItem" class="btn btn-ghost btn-sm gap-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="M12 5v14"/>
                            </svg>
                            Anadir linea
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                            <div wire:key="item-{{ $index }}" class="p-4 bg-base-200 rounded-lg">
                                <div class="flex items-start gap-4">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <!-- Description -->
                                        <div class="md:col-span-5">
                                            <label class="label text-xs">Concepto</label>
                                            <input
                                                type="text"
                                                wire:model="items.{{ $index }}.description"
                                                class="input input-sm w-full @error('items.'.$index.'.description') input-error @enderror"
                                                placeholder="Descripcion del producto o servicio"
                                            />
                                            @error('items.'.$index.'.description')
                                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Quantity -->
                                        <div class="md:col-span-2">
                                            <label class="label text-xs">Cantidad</label>
                                            <input
                                                type="number"
                                                wire:model.blur="items.{{ $index }}.quantity"
                                                class="input input-sm w-full text-right"
                                                min="0.01"
                                                step="0.01"
                                            />
                                        </div>

                                        <!-- Unit Price (in euros, converted to cents) -->
                                        <div class="md:col-span-2">
                                            <label class="label text-xs">Precio (&euro;)</label>
                                            <input
                                                type="number"
                                                wire:model.blur="items.{{ $index }}.unit_price"
                                                class="input input-sm w-full text-right"
                                                min="0"
                                                step="1"
                                                placeholder="Centimos"
                                            />
                                            <p class="text-xs text-base-content/50 mt-1">En centimos (1234 = 12,34&euro;)</p>
                                        </div>

                                        <!-- Tax Rate -->
                                        <div class="md:col-span-2">
                                            <label class="label text-xs">IVA %</label>
                                            <select wire:model.blur="items.{{ $index }}.tax_rate" class="select select-sm w-full">
                                                <option value="0">0%</option>
                                                <option value="4">4%</option>
                                                <option value="10">10%</option>
                                                <option value="21">21%</option>
                                            </select>
                                        </div>

                                        <!-- Line Total -->
                                        <div class="md:col-span-1 flex items-end justify-end">
                                            <span class="font-mono text-sm font-medium">
                                                {{ number_format($item['total_amount'] / 100, 2, ',', '.') }}&euro;
                                            </span>
                                        </div>
                                    </div>

                                    @if(count($items) > 1)
                                        <button
                                            type="button"
                                            wire:click="removeItem({{ $index }})"
                                            class="btn btn-ghost btn-sm btn-square text-error mt-6"
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('items')
                        <p class="text-error text-sm mt-2">{{ $message }}</p>
                    @enderror
                </x-ui.card>

                <!-- Notes -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Notas y condiciones</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Notas</label>
                            <textarea
                                wire:model="notes"
                                class="textarea textarea-bordered w-full h-24"
                                placeholder="Notas internas o para el cliente..."
                            ></textarea>
                        </div>
                        <div>
                            <label class="label">Condiciones de pago</label>
                            <textarea
                                wire:model="paymentTerms"
                                class="textarea textarea-bordered w-full h-24"
                                placeholder="Condiciones de pago..."
                            ></textarea>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Invoice Details -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Datos de factura</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label">Tipo de factura</label>
                            <select wire:model="serie" class="select w-full">
                                @foreach($serieTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label">Fecha factura</label>
                            <input
                                type="date"
                                wire:model="invoiceDate"
                                class="input w-full @error('invoiceDate') input-error @enderror"
                            />
                            @error('invoiceDate')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label">Fecha vencimiento</label>
                            <input
                                type="date"
                                wire:model="dueDate"
                                class="input w-full"
                            />
                        </div>

                        <div>
                            <label class="label">Fecha servicio</label>
                            <input
                                type="date"
                                wire:model="serviceDate"
                                class="input w-full"
                            />
                            <p class="text-xs text-base-content/50 mt-1">Fecha de prestacion del servicio</p>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Totals -->
                <x-ui.card>
                    <h2 class="font-semibold text-lg mb-4">Resumen</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Base imponible</dt>
                            <dd class="font-mono">{{ number_format($taxableAmount / 100, 2, ',', '.') }} &euro;</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">IVA</dt>
                            <dd class="font-mono">{{ number_format($totalTaxAmount / 100, 2, ',', '.') }} &euro;</dd>
                        </div>
                        <div class="divider my-2"></div>
                        <div class="flex justify-between text-lg font-bold">
                            <dt>Total</dt>
                            <dd class="font-mono">{{ number_format($totalAmount / 100, 2, ',', '.') }} &euro;</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Crear factura
                    </button>
                    <p class="text-xs text-base-content/50 mt-3 text-center">
                        La factura se creara como borrador
                    </p>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
