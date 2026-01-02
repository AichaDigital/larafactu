<div>
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Mi Perfil</h1>
        <p class="text-base-content/60 text-sm mt-1">Gestiona tu informacion personal y preferencias</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Info -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Informacion personal</h2>

                @if (session('profile-success'))
                    <x-ui.alert type="success" class="mb-4">
                        {{ session('profile-success') }}
                    </x-ui.alert>
                @endif

                <form wire:submit="updateProfile">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Nombre</label>
                            <input
                                type="text"
                                wire:model="name"
                                class="input w-full @error('name') input-error @enderror"
                                placeholder="Tu nombre"
                            />
                            @error('name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label">Email</label>
                            <input
                                type="email"
                                wire:model="email"
                                class="input w-full @error('email') input-error @enderror"
                                placeholder="tu@email.com"
                            />
                            @error('email')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary gap-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </x-ui.card>

            <!-- Password Change -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Cambiar contrasena</h2>

                @if (session('password-success'))
                    <x-ui.alert type="success" class="mb-4">
                        {{ session('password-success') }}
                    </x-ui.alert>
                @endif

                <form wire:submit="updatePassword">
                    <div class="space-y-4">
                        <div>
                            <label class="label">Contrasena actual</label>
                            <input
                                type="password"
                                wire:model="currentPassword"
                                class="input w-full @error('currentPassword') input-error @enderror"
                                placeholder="Tu contrasena actual"
                            />
                            @error('currentPassword')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Nueva contrasena</label>
                                <input
                                    type="password"
                                    wire:model="newPassword"
                                    class="input w-full @error('newPassword') input-error @enderror"
                                    placeholder="Nueva contrasena"
                                />
                                @error('newPassword')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="label">Confirmar nueva contrasena</label>
                                <input
                                    type="password"
                                    wire:model="newPasswordConfirmation"
                                    class="input w-full @error('newPasswordConfirmation') input-error @enderror"
                                    placeholder="Repite la nueva contrasena"
                                />
                                @error('newPasswordConfirmation')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning gap-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Cambiar contrasena
                        </button>
                    </div>
                </form>
            </x-ui.card>

            <!-- Preferences -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Preferencias</h2>

                @if (session('preferences-success'))
                    <x-ui.alert type="success" class="mb-4">
                        {{ session('preferences-success') }}
                    </x-ui.alert>
                @endif

                <form wire:submit="updatePreferences">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="label">Tema</label>
                            <select wire:model="theme" class="select w-full">
                                @foreach($availableThemes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('theme')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label">Idioma</label>
                            <select wire:model="locale" class="select w-full">
                                @foreach($availableLocales as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('locale')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label">Zona horaria</label>
                            <select wire:model="timezone" class="select w-full">
                                @foreach($availableTimezones as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary gap-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            Guardar preferencias
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Avatar / User Info -->
            <x-ui.card>
                <div class="flex flex-col items-center text-center">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24">
                            <span class="text-3xl">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <h3 class="font-semibold text-lg">{{ auth()->user()->name }}</h3>
                    <p class="text-base-content/60 text-sm">{{ auth()->user()->email }}</p>
                    <div class="mt-2">
                        @if(auth()->user()->is_admin)
                            <span class="badge badge-primary">Administrador</span>
                        @else
                            <span class="badge badge-ghost">Usuario</span>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <!-- Account Info -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Cuenta</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Registrado</dt>
                        <dd>{{ auth()->user()->created_at?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Ultimo acceso</dt>
                        <dd>{{ auth()->user()->updated_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <!-- Quick Theme Preview -->
            <x-ui.card>
                <h2 class="font-semibold text-lg mb-4">Vista rapida de temas</h2>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($availableThemes as $themeKey => $themeName)
                        <button
                            type="button"
                            wire:click="$set('theme', '{{ $themeKey }}')"
                            class="btn btn-sm {{ $theme === $themeKey ? 'btn-primary' : 'btn-ghost' }}"
                        >
                            {{ explode(' ', $themeName)[0] }}
                        </button>
                    @endforeach
                </div>
                <p class="text-xs text-base-content/50 mt-3">
                    Haz clic en un tema para previsualizarlo, luego guarda tus preferencias.
                </p>
            </x-ui.card>
        </div>
    </div>
</div>
