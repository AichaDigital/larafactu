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
        // TODO: In production, implement role/permission verification
        return App::environment('local');
    }
}
