<x-layouts.guest>
    <x-slot name="title">Iniciar sesion</x-slot>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title justify-center text-2xl">Iniciar sesion</h2>

            <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-4">
                @csrf

                <!-- Email -->
                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">Email</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="input input-bordered @error('email') input-error @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    />
                    @error('email')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Contrasena</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input input-bordered @error('password') input-error @enderror"
                        required
                    />
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                        <span class="label-text">Recordarme</span>
                    </label>
                </div>

                <!-- Submit -->
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">
                        Iniciar sesion
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
