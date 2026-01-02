<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Clientes</h1>
            <p class="text-base-content/60 text-sm mt-1">Gestiona tus clientes y sus datos fiscales</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary gap-2" wire:navigate>
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>
            </svg>
            Nuevo cliente
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
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label class="input w-full">
                    <svg class="size-5 text-base-content/60" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre, email o NIF..."
                        class="grow"
                    />
                </label>
            </div>

            <!-- Filter -->
            <div class="w-full sm:w-48">
                <select wire:model.live="filter" class="select w-full">
                    <option value="all">Todos</option>
                    <option value="company">Empresas</option>
                    <option value="individual">Particulares</option>
                </select>
            </div>
        </div>
    </x-ui.card>

    <!-- Results -->
    <x-ui.card>
        @if($customers->isEmpty())
            <x-ui.empty-state
                title="No hay clientes"
                description="Crea tu primer cliente para empezar a facturar"
                action-label="Nuevo cliente"
                action-href="{{ route('customers.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>NIF/CIF</th>
                            <th>Tipo</th>
                            <th>Localidad</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            @php
                                $profile = $taxProfiles->get($customer->id);
                            @endphp
                            <tr wire:key="customer-{{ $customer->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content w-10 rounded-full">
                                                <span class="text-sm">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $customer->name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $customer->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($profile && $profile->tax_id)
                                        <span class="font-mono text-sm">{{ $profile->tax_id }}</span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($profile)
                                        @if($profile->is_company)
                                            <span class="badge badge-info badge-sm">Empresa</span>
                                        @else
                                            <span class="badge badge-ghost badge-sm">Particular</span>
                                        @endif
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($profile && $profile->city)
                                        <span class="text-sm">{{ $profile->city }}</span>
                                        @if($profile->country_code !== 'ES')
                                            <span class="text-xs text-base-content/60">({{ $profile->country_code }})</span>
                                        @endif
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a
                                            href="{{ route('customers.edit', $customer) }}"
                                            class="btn btn-ghost btn-sm btn-square"
                                            title="Editar"
                                            wire:navigate
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                                            </svg>
                                        </a>
                                        <button
                                            wire:click="delete('{{ $customer->id }}')"
                                            wire:confirm="¿Estas seguro de eliminar este cliente?"
                                            class="btn btn-ghost btn-sm btn-square text-error"
                                            title="Eliminar"
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="mt-4 border-t border-base-200 pt-4">
                    {{ $customers->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
