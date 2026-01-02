{{--
    Navbar component - Nexus style
    ADR-005: DaisyUI semantic classes + Lucide icons

    Two sidebar controls in navbar:
    - layout-sidebar-toggle-trigger: Direct open/close (shown when sidebar is NOT in hover mode)
    - layout-sidebar-hover-trigger: Exits hover mode (shown when sidebar IS in hover mode)
--}}
<div id="layout-topbar" role="navigation" aria-label="Navbar" class="flex items-center justify-between px-3 bg-base-100 border-b border-base-300 min-h-16">
    {{-- Navbar Start: Sidebar toggle + Search --}}
    <div class="inline-flex items-center gap-3">
        {{-- Direct sidebar toggle (shown when NOT in hover mode) --}}
        <label
            class="btn btn-square btn-ghost btn-sm group-has-[[id=layout-sidebar-hover-trigger]:checked]/html:hidden"
            aria-label="Toggle sidebar"
            for="layout-sidebar-toggle-trigger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" x2="20" y1="12" y2="12"/>
                <line x1="4" x2="20" y1="6" y2="6"/>
                <line x1="4" x2="20" y1="18" y2="18"/>
            </svg>
        </label>

        {{-- Exit hover mode (shown when IN hover mode) --}}
        <label
            class="btn btn-square btn-ghost btn-sm hidden group-has-[[id=layout-sidebar-hover-trigger]:checked]/html:flex"
            aria-label="Exit hover mode"
            for="layout-sidebar-hover-trigger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" x2="20" y1="12" y2="12"/>
                <line x1="4" x2="20" y1="6" y2="6"/>
                <line x1="4" x2="20" y1="18" y2="18"/>
            </svg>
        </label>

        {{-- Search button (desktop) --}}
        <button
            class="btn btn-outline btn-sm btn-ghost border-base-300 text-base-content/70 hidden h-9 w-48 justify-start gap-2 !text-sm md:flex"
            onclick="document.getElementById('search-modal')?.showModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <span>Buscar...</span>
        </button>

        {{-- Search button (mobile) --}}
        <button
            class="btn btn-outline btn-sm btn-square btn-ghost border-base-300 text-base-content/70 flex size-9 md:hidden"
            aria-label="Buscar"
            onclick="document.getElementById('search-modal')?.showModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
        </button>
    </div>

    {{-- Navbar End: Theme toggle + User menu --}}
    <div class="inline-flex items-center gap-2">
        {{-- Theme selector --}}
        <livewire:theme-selector />

        {{-- User menu --}}
        @auth
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="avatar placeholder">
                        <div class="bg-primary/10 text-primary w-9 rounded-full">
                            <span class="text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                    </div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow border border-base-300">
                    {{-- User info header --}}
                    <li class="menu-title">
                        <span>{{ auth()->user()->name }}</span>
                    </li>
                    <li class="disabled text-xs opacity-60 px-4 pb-2">{{ auth()->user()->email }}</li>

                    {{-- Menu items --}}
                    <li>
                        <a href="{{ route('profile') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Perfil
                        </a>
                    </li>

                    @if(auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('admin.dashboard') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Administracion
                            </a>
                        </li>
                    @endif

                    <div class="divider my-1"></div>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-error w-full text-left flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" x2="9" y1="12" y2="12"/>
                                </svg>
                                Cerrar sesion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                Iniciar sesion
            </a>
        @endauth
    </div>
</div>

{{-- Search Modal --}}
<dialog id="search-modal" class="modal p-0">
    <div class="modal-box bg-transparent p-0 shadow-none">
        <div class="bg-base-100 rounded-box">
            <div class="input w-full border-0 !outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-base-content/60 size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                    type="search"
                    class="grow"
                    placeholder="Buscar facturas, clientes, articulos..."
                    aria-label="Buscar" />
                <form method="dialog">
                    <button class="btn btn-xs btn-circle btn-ghost" aria-label="Cerrar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="text-base-content/80 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </form>
            </div>
            <div class="border-base-300 flex items-center gap-3 border-t px-2 py-2">
                <p class="text-base-content/80 ms-1 text-sm">Escribe para buscar</p>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
