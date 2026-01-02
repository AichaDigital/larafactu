<div>
    <h3 class="text-xl font-semibold text-center">Crear cuenta</h3>
    <p class="text-base-content/70 mt-2 text-center text-sm">
        Registrate para empezar a gestionar tu facturacion
    </p>

    <form wire:submit="register" class="mt-6 md:mt-10">
        <!-- Name -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Nombre</legend>
            <label class="input w-full focus:outline-0 @error('name') input-error @enderror">
                <!-- Lucide user icon -->
                <svg class="text-base-content/80 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <input
                    type="text"
                    wire:model="name"
                    placeholder="Tu nombre completo"
                    required
                    autofocus
                    class="grow focus:outline-0"
                />
            </label>
            @error('name')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Email -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Email</legend>
            <label class="input w-full focus:outline-0 @error('email') input-error @enderror">
                <!-- Lucide mail icon -->
                <svg class="text-base-content/80 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                <input
                    type="email"
                    wire:model="email"
                    placeholder="tu@email.com"
                    required
                    class="grow focus:outline-0"
                />
            </label>
            @error('email')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Password -->
        <fieldset class="fieldset" x-data="{ show: false }">
            <legend class="fieldset-legend">Contrasena</legend>
            <label class="input w-full focus:outline-0 @error('password') input-error @enderror">
                <!-- Lucide key-round icon -->
                <svg class="text-base-content/80 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/>
                </svg>
                <input
                    :type="show ? 'text' : 'password'"
                    wire:model="password"
                    placeholder="Minimo 8 caracteres"
                    required
                    minlength="8"
                    class="grow focus:outline-0"
                />
                <button
                    type="button"
                    class="btn btn-xs btn-ghost btn-circle text-base-content/60"
                    @click="show = !show"
                    aria-label="Mostrar contrasena"
                >
                    <!-- Lucide eye icon -->
                    <svg x-show="!show" class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <!-- Lucide eye-off icon -->
                    <svg x-show="show" class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>
                    </svg>
                </button>
            </label>
            @error('password')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Password Confirmation -->
        <fieldset class="fieldset" x-data="{ show: false }">
            <legend class="fieldset-legend">Confirmar contrasena</legend>
            <label class="input w-full focus:outline-0 @error('password_confirmation') input-error @enderror">
                <!-- Lucide shield-check icon -->
                <svg class="text-base-content/80 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
                </svg>
                <input
                    :type="show ? 'text' : 'password'"
                    wire:model="password_confirmation"
                    placeholder="Repite la contrasena"
                    required
                    minlength="8"
                    class="grow focus:outline-0"
                />
                <button
                    type="button"
                    class="btn btn-xs btn-ghost btn-circle text-base-content/60"
                    @click="show = !show"
                    aria-label="Mostrar contrasena"
                >
                    <!-- Lucide eye icon -->
                    <svg x-show="!show" class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <!-- Lucide eye-off icon -->
                    <svg x-show="show" class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>
                    </svg>
                </button>
            </label>
            @error('password_confirmation')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Terms -->
        <div class="mt-4 flex items-start gap-3">
            <input
                type="checkbox"
                wire:model="terms"
                id="terms"
                class="checkbox checkbox-sm checkbox-primary mt-0.5"
            />
            <label for="terms" class="text-sm cursor-pointer">
                Acepto los <a href="#" class="link link-primary">terminos y condiciones</a>
                y la <a href="#" class="link link-primary">politica de privacidad</a>
            </label>
        </div>
        @error('terms')
            <p class="text-error text-xs mt-1">{{ $message }}</p>
        @enderror

        <!-- Submit -->
        <button
            type="submit"
            class="btn btn-primary w-full mt-6 gap-3"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="register" class="flex items-center gap-2">
                <!-- Lucide user-plus icon -->
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>
                </svg>
                Crear cuenta
            </span>
            <span wire:loading wire:target="register" class="loading loading-spinner loading-sm"></span>
        </button>

        <!-- Login link -->
        <p class="text-center text-sm mt-4 text-base-content/70">
            Ya tienes cuenta?
            <a href="{{ route('login') }}" class="link link-primary" wire:navigate>
                Inicia sesion
            </a>
        </p>
    </form>
</div>
