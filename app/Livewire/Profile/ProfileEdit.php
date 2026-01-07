<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\UserPreference;
use App\Services\AvatarService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Mi Perfil')]
class ProfileEdit extends Component
{
    use WithFileUploads;

    // Personal info
    public string $name = '';

    public string $email = '';

    // Avatar
    public $avatarFile = null;

    // Password change
    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    // Preferences
    public string $theme = 'cupcake';

    public string $locale = 'es';

    public string $timezone = 'Europe/Madrid';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;

        $preferences = $user->getPreferences();
        $this->theme = $preferences->theme;
        $this->locale = $preferences->locale;
        $this->timezone = $preferences->timezone;
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.Auth::id(),
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email valido.',
            'email.unique' => 'Este email ya esta en uso.',
        ]);

        Auth::user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('profile-success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => ['required', 'string', Password::min(8)],
            'newPasswordConfirmation' => 'required|same:newPassword',
        ], [
            'currentPassword.required' => 'Introduce tu contrasena actual.',
            'newPassword.required' => 'Introduce la nueva contrasena.',
            'newPassword.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'newPasswordConfirmation.required' => 'Confirma la nueva contrasena.',
            'newPasswordConfirmation.same' => 'Las contrasenas no coinciden.',
        ]);

        if (! Hash::check($this->currentPassword, Auth::user()->password)) {
            $this->addError('currentPassword', 'La contrasena actual no es correcta.');

            return;
        }

        Auth::user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);

        session()->flash('password-success', 'Contrasena actualizada correctamente.');
    }

    public function uploadAvatar(): void
    {
        $this->validate([
            'avatarFile' => 'required|image|max:2048', // 2MB max
        ], [
            'avatarFile.required' => 'Selecciona una imagen.',
            'avatarFile.image' => 'El archivo debe ser una imagen.',
            'avatarFile.max' => 'La imagen no puede superar 2MB.',
        ]);

        $avatarService = app(AvatarService::class);
        $avatarService->uploadAvatar(Auth::user(), $this->avatarFile);

        $this->reset('avatarFile');

        session()->flash('avatar-success', 'Avatar actualizado correctamente.');
    }

    public function deleteAvatar(): void
    {
        $avatarService = app(AvatarService::class);
        $avatarService->deleteAvatar(Auth::user());

        session()->flash('avatar-success', 'Avatar eliminado. Se usara el avatar por defecto.');
    }

    public function updatePreferences(): void
    {
        $this->validate([
            'theme' => 'required|string|in:'.implode(',', UserPreference::AVAILABLE_THEMES),
            'locale' => 'required|string|in:'.implode(',', UserPreference::AVAILABLE_LOCALES),
            'timezone' => 'required|string|max:50',
        ], [
            'theme.required' => 'Selecciona un tema.',
            'theme.in' => 'Tema no valido.',
            'locale.required' => 'Selecciona un idioma.',
            'locale.in' => 'Idioma no valido.',
            'timezone.required' => 'Selecciona una zona horaria.',
        ]);

        $preferences = Auth::user()->getPreferences();
        $preferences->update([
            'theme' => $this->theme,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ]);

        // Update session theme
        session(['theme' => $this->theme]);

        session()->flash('preferences-success', 'Preferencias actualizadas correctamente.');

        // Dispatch event to update theme in browser
        $this->dispatch('theme-changed', theme: $this->theme);
    }

    public function render(): View
    {
        return view('livewire.profile.profile-edit', [
            'availableThemes' => $this->getAvailableThemes(),
            'availableLocales' => $this->getAvailableLocales(),
            'availableTimezones' => $this->getAvailableTimezones(),
        ]);
    }

    protected function getAvailableThemes(): array
    {
        return [
            'cupcake' => 'Cupcake (Claro)',
            'corporate' => 'Corporate (Profesional)',
            'abyss' => 'Abyss (Oscuro)',
            'sunset' => 'Sunset (Oscuro calido)',
        ];
    }

    protected function getAvailableLocales(): array
    {
        return [
            'es' => 'Espanol',
            'en' => 'English',
            'ca' => 'Catala',
            'eu' => 'Euskara',
            'gl' => 'Galego',
        ];
    }

    protected function getAvailableTimezones(): array
    {
        return [
            'Europe/Madrid' => 'Madrid (UTC+1/+2)',
            'Atlantic/Canary' => 'Canarias (UTC+0/+1)',
            'Europe/London' => 'Londres (UTC+0/+1)',
            'Europe/Paris' => 'Paris (UTC+1/+2)',
            'Europe/Berlin' => 'Berlin (UTC+1/+2)',
            'America/New_York' => 'Nueva York (UTC-5/-4)',
            'America/Los_Angeles' => 'Los Angeles (UTC-8/-7)',
            'America/Mexico_City' => 'Mexico DF (UTC-6/-5)',
            'America/Bogota' => 'Bogota (UTC-5)',
            'America/Buenos_Aires' => 'Buenos Aires (UTC-3)',
            'America/Sao_Paulo' => 'Sao Paulo (UTC-3)',
        ];
    }
}
