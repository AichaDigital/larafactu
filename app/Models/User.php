<?php

namespace App\Models;

use AichaDigital\Larabill\Concerns\HasUuid;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    /**
     * Create a new Eloquent query builder for the model.
     *
     * Uses custom BinaryUuidBuilder to handle UUID binary conversions in relationships.
     * This is CRITICAL for relationships with Larabill models (Invoice, FiscalSettings, etc.)
     * that reference user_id as binary UUID.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \AichaDigital\Larabill\Database\Query\BinaryUuidBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new \AichaDigital\Larabill\Database\Query\BinaryUuidBuilder($query);
    }

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
