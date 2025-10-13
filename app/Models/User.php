<?php

namespace App\Models;

use Dyrynda\Database\Support\BindsOnUuid;
use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BindsOnUuid, GeneratesUuid, HasFactory, Notifiable;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The UUID version to use (ordered UUID v7).
     */
    public function uuidVersion(): string
    {
        return 'uuid7';
    }

    /**
     * The column name for the UUID.
     */
    public function uuidColumn(): string
    {
        return 'id';
    }

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
            'id' => EfficientUuid::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the authentication identifier (raw binary value for UUID).
     * This is crucial for Laravel Auth to work with binary UUID.
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->getRawOriginal($this->getAuthIdentifierName());
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
