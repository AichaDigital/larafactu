<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Recuperar contrasena')]
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $emailSent = false;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email valido.',
        ];
    }

    public function sendResetLink(): void
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->emailSent = true;
            session()->flash('status', 'Te hemos enviado un enlace para restablecer tu contrasena.');
        } else {
            $this->addError('email', $this->translateStatus($status));
        }
    }

    protected function translateStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'No encontramos un usuario con ese email.',
            Password::RESET_THROTTLED => 'Espera antes de intentarlo de nuevo.',
            default => 'Error al enviar el enlace de recuperacion.',
        };
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
