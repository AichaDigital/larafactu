<?php

namespace App\Models;

use AichaDigital\Larabill\Concerns\HasUuid;
use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\LegalEntityType;
use AichaDigital\Larabill\Models\UserTaxProfile;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'parent_user_id',
        'relationship_type',
        'display_name',
        'legal_entity_type_code',
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
            'relationship_type' => UserRelationshipType::class,
        ];
    }

    // ========================================
    // RELATIONSHIPS (ADR-003)
    // ========================================

    /**
     * Get the parent user (for delegated users).
     *
     * @return BelongsTo<User, $this>
     */
    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /**
     * Get all delegated users (children) for this user.
     *
     * @return HasMany<User, $this>
     */
    public function delegatedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'parent_user_id');
    }

    /**
     * Get the legal entity type for this user.
     *
     * @return BelongsTo<LegalEntityType, $this>
     */
    public function legalEntityType(): BelongsTo
    {
        return $this->belongsTo(LegalEntityType::class, 'legal_entity_type_code', 'code');
    }

    /**
     * Get all tax profiles for this user (historical + active).
     *
     * @return HasMany<UserTaxProfile, $this>
     */
    public function taxProfiles(): HasMany
    {
        return $this->hasMany(UserTaxProfile::class, 'user_id');
    }

    /**
     * Get the current active tax profile for this user.
     *
     * Helper method for convenience.
     */
    public function currentTaxProfile(): ?UserTaxProfile
    {
        return UserTaxProfile::getValidForUserAt($this->id, now());
    }

    /**
     * Get tax profile valid at a specific date.
     */
    public function taxProfileAt(\Carbon\Carbon $date): ?UserTaxProfile
    {
        return UserTaxProfile::getValidForUserAt($this->id, $date);
    }

    /**
     * Update tax profile for this user (creates new record, closes previous).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateTaxProfile(array $attributes): UserTaxProfile
    {
        // Close previous profile
        $current = $this->currentTaxProfile();
        if ($current) {
            $current->valid_until = now()->subDay();
            $current->save();
        }

        // Create new profile
        return UserTaxProfile::create([
            'user_id' => $this->id,
            'valid_from' => now(),
            'valid_until' => null,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    // ========================================
    // HELPER METHODS (ADR-003 Fase 2)
    // ========================================

    /**
     * Check if this user is a direct client (no parent).
     */
    public function isDirect(): bool
    {
        return $this->parent_user_id === null;
    }

    /**
     * Check if this user is delegated to another user.
     */
    public function isDelegated(): bool
    {
        return $this->parent_user_id !== null;
    }

    /**
     * Get the name to use for billing purposes.
     *
     * Priority: display_name > name
     */
    public function billableName(): string
    {
        return $this->display_name ?? $this->name;
    }

    /**
     * Check if this user has any delegated users.
     */
    public function hasDelegatedUsers(): bool
    {
        return $this->delegatedUsers()->exists();
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

            $emailPart = strrchr($this->email, '@');
            $userDomain = $emailPart !== false ? substr($emailPart, 1) : '';

            if (in_array($userDomain, $domainList, true)) {
                return true;
            }
        }

        return false;
    }
}
