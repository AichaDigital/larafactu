<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Articulos</h1>
            <p class="text-base-content/60 text-sm mt-1">Gestiona tu catalogo de productos y servicios</p>
        </div>
        <a href="{{ route('articles.create') }}" class="btn btn-primary gap-2" wire:navigate>
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/><path d="M12 5v14"/>
            </svg>
            Nuevo articulo
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
                        placeholder="Buscar por codigo, nombre..."
                        class="grow"
                    />
                </label>
            </div>

            <!-- Type filter -->
            <div class="w-full lg:w-40">
                <select wire:model.live="type" class="select w-full">
                    <option value="">Todos los tipos</option>
                    <option value="0">Producto</option>
                    <option value="1">Servicio</option>
                </select>
            </div>

            <!-- Category filter -->
            @if(count($categories) > 0)
                <div class="w-full lg:w-40">
                    <select wire:model.live="category" class="select w-full">
                        <option value="">Todas las categorias</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Status filter -->
            <div class="w-full lg:w-32">
                <select wire:model.live="status" class="select w-full">
                    <option value="">Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
        </div>
    </x-ui.card>

    <!-- Results -->
    <x-ui.card>
        @if($articles->isEmpty())
            <x-ui.empty-state
                title="No hay articulos"
                description="Crea tu primer articulo para empezar a facturar"
                action-label="Nuevo articulo"
                action-href="{{ route('articles.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Categoria</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                            <tr wire:key="article-{{ $article->id }}">
                                <td>
                                    <span class="font-mono font-medium">{{ $article->code }}</span>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $article->getTranslation('name', 'es') ?: $article->name }}</div>
                                    @if($article->description)
                                        <div class="text-sm text-base-content/60 truncate max-w-xs">
                                            {{ Str::limit($article->getTranslation('description', 'es') ?: $article->description, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $typeValue = $article->item_type instanceof \AichaDigital\Larabill\Enums\ItemType
                                            ? $article->item_type->value
                                            : $article->item_type;
                                    @endphp
                                    @if($typeValue === 0)
                                        <span class="badge badge-primary badge-sm">Producto</span>
                                    @else
                                        <span class="badge badge-secondary badge-sm">Servicio</span>
                                    @endif
                                </td>
                                <td>
                                    @if($article->category)
                                        <span class="badge badge-ghost badge-sm">{{ $article->category }}</span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button
                                        wire:click="toggleActive({{ $article->id }})"
                                        class="btn btn-ghost btn-xs gap-1"
                                        title="{{ $article->is_active ? 'Desactivar' : 'Activar' }}"
                                    >
                                        @if($article->is_active)
                                            <span class="badge badge-success badge-xs"></span>
                                            <span class="text-success">Activo</span>
                                        @else
                                            <span class="badge badge-ghost badge-xs"></span>
                                            <span class="text-base-content/60">Inactivo</span>
                                        @endif
                                    </button>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a
                                            href="{{ route('articles.edit', $article) }}"
                                            class="btn btn-ghost btn-sm btn-square"
                                            title="Editar"
                                            wire:navigate
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                                            </svg>
                                        </a>
                                        <button
                                            wire:click="delete({{ $article->id }})"
                                            wire:confirm="¿Estas seguro de eliminar este articulo?"
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
            @if($articles->hasPages())
                <div class="mt-4 border-t border-base-200 pt-4">
                    {{ $articles->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
