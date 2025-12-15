<?php

namespace App\Models;

use AichaDigital\Larabill\Concerns\HasUuid;
use AichaDigital\Larabill\Models\CustomerFiscalData;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuid, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========================================
    // RELATIONSHIPS (ADR-001)
    // ========================================

    /**
     * Get all fiscal data records for this user (historical + active).
     *
     * @return HasMany<CustomerFiscalData, $this>
     */
    public function fiscalData(): HasMany
    {
        return $this->hasMany(CustomerFiscalData::class, 'user_id');
    }

    /**
     * Get the current active fiscal data for this user.
     *
     * Helper method for convenience.
     */
    public function currentFiscalData(): ?CustomerFiscalData
    {
        return CustomerFiscalData::getActiveForUser($this->id);
    }

    /**
     * Get fiscal data valid at a specific date.
     */
    public function fiscalDataAt(\Carbon\Carbon $date): ?CustomerFiscalData
    {
        return CustomerFiscalData::getValidForUserAt($this->id, $date);
    }

    /**
     * Update fiscal data for this user (creates new record, closes previous).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateFiscalData(array $attributes): CustomerFiscalData
    {
        return CustomerFiscalData::createForUser($this->id, $attributes);
    }

    // ========================================
    // ADMIN PANEL ACCESS CONTROL
    // ========================================

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // In local development, allow access to all users
        if (App::environment('local')) {
            return true;
        }

        // Production: Check against allowed emails and domains
        return $this->isAllowedAdminUser();
    }

    /**
     * Check if user is allowed to access admin panel.
     */
    protected function isAllowedAdminUser(): bool
    {
        // Get allowed emails (comma-separated)
        $allowedEmails = config('app.admin_emails', '');
        if (! empty($allowedEmails)) {
            $emailList = array_map('trim', explode(',', $allowedEmails));
            if (in_array($this->email, $emailList, true)) {
                return true;
            }
        }

        // Get allowed domains (comma-separated)
        $allowedDomains = config('app.admin_domains', '');
        if (! empty($allowedDomains)) {
            // Normalize domains: remove @ prefix if present
            $domainList = array_map(function ($domain) {
                $domain = trim($domain);

                return ltrim($domain, '@');
            }, explode(',', $allowedDomains));

            $userDomain = substr(strrchr($this->email, '@'), 1);

            if (in_array($userDomain, $domainList, true)) {
                return true;
            }
        }

        return false;
    }
}
