{{--
    Sidebar component - Nexus style with collapsible menus
    ADR-005: DaisyUI semantic classes + Lucide icons

    Two sidebar controls:
    - layout-sidebar-hover-trigger: Auto-hide mode (hover to show)
    - layout-sidebar-toggle-trigger: Direct collapse (mobile + manual toggle)
--}}
<aside id="layout-sidebar" class="sidebar-menu bg-base-100 min-h-full w-64 flex flex-col border-r border-base-300">
    {{-- Logo + Sidebar hover toggle --}}
    <div class="flex min-h-16 items-center justify-between gap-3 ps-5 pe-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <div class="bg-primary text-primary-content rounded-lg p-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                    <path d="M8 18v-2"/><path d="M12 18v-4"/><path d="M16 18v-6"/>
                </svg>
            </div>
            <span class="text-lg font-semibold">{{ config('app.name', 'Larafactu') }}</span>
        </a>
        {{-- Sidebar hover mode toggle (desktop only) - switches between normal and auto-hide --}}
        <label
            for="layout-sidebar-hover-trigger"
            title="Modo auto-ocultar"
            class="btn btn-circle btn-ghost btn-sm text-base-content/50 relative max-lg:hidden">
            {{-- Panel close icon (shown when sidebar is normal) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 absolute opacity-100 transition-all duration-300 group-has-[[id=layout-sidebar-hover-trigger]:checked]/html:opacity-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2"/>
                <path d="M9 3v18"/>
                <path d="m14 9-3 3 3 3"/>
            </svg>
            {{-- Panel dashed icon (shown when sidebar is in hover mode) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 absolute opacity-0 transition-all duration-300 group-has-[[id=layout-sidebar-hover-trigger]:checked]/html:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2"/>
                <path d="M9 3v18" stroke-dasharray="4 4"/>
            </svg>
        </label>
    </div>

    {{-- Navigation - main scrollable area --}}
    <nav class="relative min-h-0 grow">
        <div class="size-full overflow-y-auto px-2.5 pb-3">
            {{-- Principal section --}}
            <p class="menu-label px-2.5 pt-3 pb-1.5 first:pt-0">Principal</p>

            <div class="space-y-0.5">
                <a href="{{ route('dashboard') }}" @class([
                    'menu-item',
                    'active' => request()->routeIs('dashboard'),
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1"/>
                        <rect width="7" height="5" x="14" y="3" rx="1"/>
                        <rect width="7" height="9" x="14" y="12" rx="1"/>
                        <rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    <span class="grow">Dashboard</span>
                </a>
            </div>

            {{-- Facturacion section --}}
            <p class="menu-label px-2.5 pt-4 pb-1.5">Facturacion</p>

            <div class="space-y-0.5">
                {{-- Facturas group (collapsible) --}}
                <div class="group collapse">
                    <input
                        type="checkbox"
                        class="peer"
                        name="sidebar-menu-parent-item"
                        aria-label="Sidemenu item trigger"
                        {{ request()->routeIs('invoices.*') ? 'checked' : '' }} />
                    <div class="collapse-title px-2.5 py-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                            <path d="M10 12h4"/><path d="M10 16h4"/><path d="M10 8h1"/>
                        </svg>
                        <span class="grow">Facturas</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="arrow-icon size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </div>
                    <div class="collapse-content ms-6.5 !p-0">
                        <div class="mt-0.5 space-y-0.5">
                            <a href="{{ route('invoices.index') }}" @class([
                                'menu-item',
                                'active' => request()->routeIs('invoices.index'),
                            ])>
                                <span class="grow">Listado</span>
                            </a>
                            <a href="{{ route('invoices.create') }}" @class([
                                'menu-item',
                                'active' => request()->routeIs('invoices.create'),
                            ])>
                                <span class="grow">Nueva factura</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Clientes --}}
                <a href="{{ route('customers.index') }}" @class([
                    'menu-item',
                    'active' => request()->routeIs('customers.*'),
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="grow">Clientes</span>
                </a>

                {{-- Articulos --}}
                <a href="{{ route('articles.index') }}" @class([
                    'menu-item',
                    'active' => request()->routeIs('articles.*'),
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m7.5 4.27 9 5.15"/>
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
                    </svg>
                    <span class="grow">Articulos</span>
                </a>
            </div>

            {{-- Admin section --}}
            @if(auth()->check() && auth()->user()->isAdmin())
                <p class="menu-label px-2.5 pt-4 pb-1.5">Administracion</p>

                <div class="space-y-0.5">
                    {{-- Admin group (collapsible) --}}
                    <div class="group collapse">
                        <input
                            type="checkbox"
                            class="peer"
                            name="sidebar-menu-parent-item"
                            aria-label="Sidemenu item trigger"
                            {{ request()->routeIs('admin.*') ? 'checked' : '' }} />
                        <div class="collapse-title px-2.5 py-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <span class="grow">Sistema</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="arrow-icon size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </div>
                        <div class="collapse-content ms-6.5 !p-0">
                            <div class="mt-0.5 space-y-0.5">
                                <a href="{{ route('admin.dashboard') }}" @class([
                                    'menu-item',
                                    'active' => request()->routeIs('admin.dashboard'),
                                ])>
                                    <span class="grow">Configuracion</span>
                                </a>
                                <a href="{{ route('admin.users') }}" @class([
                                    'menu-item',
                                    'active' => request()->routeIs('admin.users'),
                                ])>
                                    <span class="grow">Usuarios</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </nav>

    {{-- Footer section (Nexus style) --}}
    <div class="mt-auto">
        {{-- Components link --}}
        <div class="px-2.5 pb-2">
            <a href="#" class="menu-item text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M12 1v4"/>
                    <path d="M12 19v4"/>
                    <path d="m4.6 4.6 2.8 2.8"/>
                    <path d="m16.6 16.6 2.8 2.8"/>
                    <path d="M1 12h4"/>
                    <path d="M19 12h4"/>
                    <path d="m4.6 19.4 2.8-2.8"/>
                    <path d="m16.6 7.4 2.8-2.8"/>
                </svg>
                <span class="grow">Componentes</span>
            </a>
        </div>

        {{-- User profile card --}}
        @auth
            <div class="border-t border-base-300 p-3">
                <div class="dropdown dropdown-top w-full">
                    <div tabindex="0" role="button" class="flex w-full items-center gap-3 rounded-lg p-2 hover:bg-base-200 transition-colors cursor-pointer">
                        {{-- Avatar --}}
                        <x-avatar :user="auth()->user()" size="md" />
                        {{-- Name and handle --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-base-content/60 truncate">{{ '@' . Str::slug(auth()->user()->name) }}</p>
                        </div>
                        {{-- Chevron --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-base-content/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7 15 5 5 5-5"/>
                            <path d="m7 9 5-5 5 5"/>
                        </svg>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-full p-2 shadow border border-base-300 mb-2">
                        <li>
                            <a href="{{ route('profile') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Mi perfil
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="text-error w-full text-left flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            </div>
        @else
            <div class="border-t border-base-300 p-4">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-full">
                    Iniciar sesion
                </a>
            </div>
        @endauth
    </div>
</aside>
