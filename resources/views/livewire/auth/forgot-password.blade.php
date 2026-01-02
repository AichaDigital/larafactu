<div>
    <h3 class="text-xl font-semibold text-center">Recuperar contrasena</h3>
    <p class="text-base-content/70 mt-2 text-center text-sm">
        Introduce tu email y te enviaremos un enlace para restablecer tu contrasena
    </p>

    @if($emailSent)
        <div class="mt-6 md:mt-10">
            <x-ui.alert type="success">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h8"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="m16 19 2 2 4-4"/>
                </svg>
                <span>Revisa tu bandeja de entrada. Te hemos enviado un enlace para restablecer tu contrasena.</span>
            </x-ui.alert>

            <p class="text-center text-sm mt-6 text-base-content/70">
                No has recibido el email?
                <button
                    type="button"
                    wire:click="$set('emailSent', false)"
                    class="link link-primary"
                >
                    Intentar de nuevo
                </button>
            </p>
        </div>
    @else
        <form wire:submit="sendResetLink" class="mt-6 md:mt-10">
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

            <!-- Submit -->
            <button
                type="submit"
                class="btn btn-primary w-full mt-6 gap-3"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="sendResetLink" class="flex items-center gap-2">
                    <!-- Lucide send icon -->
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>
                    </svg>
                    Enviar enlace de recuperacion
                </span>
                <span wire:loading wire:target="sendResetLink" class="loading loading-spinner loading-sm"></span>
            </button>

            <!-- Back to login -->
            <p class="text-center text-sm mt-4 text-base-content/70">
                Recuerdas tu contrasena?
                <a href="{{ route('login') }}" class="link link-primary" wire:navigate>
                    Iniciar sesion
                </a>
            </p>
        </form>
    @endif
</div>
