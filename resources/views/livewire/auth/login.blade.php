<div>
    <h3 class="text-xl font-semibold text-center">Iniciar sesion</h3>
    <p class="text-base-content/70 mt-2 text-center text-sm">
        Accede a tu cuenta para gestionar tu facturacion
    </p>

    @if (session('status'))
        <div class="mt-4">
            <x-ui.alert type="success">
                {{ session('status') }}
            </x-ui.alert>
        </div>
    @endif

    <form wire:submit="login" class="mt-6 md:mt-10">
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
                    autofocus
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
                    placeholder="********"
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

        <!-- Remember me & Forgot password -->
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    wire:model="remember"
                    id="remember"
                    class="checkbox checkbox-sm checkbox-primary"
                />
                <label for="remember" class="text-sm cursor-pointer">
                    Recordarme
                </label>
            </div>
            <a href="{{ route('password.request') }}" class="text-sm link link-primary" wire:navigate>
                Olvidaste tu contrasena?
            </a>
        </div>

        <!-- Submit -->
        <button
            type="submit"
            class="btn btn-primary w-full mt-6 gap-3"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                <!-- Lucide log-in icon -->
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/>
                </svg>
                Iniciar sesion
            </span>
            <span wire:loading wire:target="login" class="loading loading-spinner loading-sm"></span>
        </button>

        <!-- Register link -->
        <p class="text-center text-sm mt-4 text-base-content/70">
            No tienes cuenta?
            <a href="{{ route('register') }}" class="link link-primary" wire:navigate>
                Registrate
            </a>
        </p>
    </form>
</div>
