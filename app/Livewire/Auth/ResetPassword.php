<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Restablecer contrasena')]
class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email valido.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ];
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', 'Tu contrasena ha sido restablecida correctamente.');
            $this->redirect(route('login'), navigate: true);
        } else {
            $this->addError('email', $this->translateStatus($status));
        }
    }

    protected function translateStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'No encontramos un usuario con ese email.',
            Password::INVALID_TOKEN => 'El enlace de recuperacion ha expirado o no es valido.',
            Password::RESET_THROTTLED => 'Espera antes de intentarlo de nuevo.',
            default => 'Error al restablecer la contrasena.',
        };
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
