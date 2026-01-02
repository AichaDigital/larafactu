<?php

namespace App\Models;

use AichaDigital\Larabill\Concerns\HasUuid;
use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\LegalEntityType;
use AichaDigital\Larabill\Models\UserTaxProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'current_tax_profile_id',
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
     * Get the tax profile currently assigned to this user.
     *
     * ADR-004: Users point to a tax profile via current_tax_profile_id.
     * Multiple users can share the same profile.
     *
     * @return BelongsTo<UserTaxProfile, $this>
     */
    public function currentTaxProfileRelation(): BelongsTo
    {
        return $this->belongsTo(UserTaxProfile::class, 'current_tax_profile_id');
    }

    /**
     * Get all tax profiles owned by this user (historical + active).
     *
     * ADR-004: Changed FK from user_id to owner_user_id.
     *
     * @return HasMany<UserTaxProfile, $this>
     */
    public function ownedTaxProfiles(): HasMany
    {
        return $this->hasMany(UserTaxProfile::class, 'owner_user_id');
    }

    /**
     * Alias for ownedTaxProfiles().
     *
     * @deprecated Use ownedTaxProfiles() instead. Will be removed in v2.0.
     *
     * @return HasMany<UserTaxProfile, $this>
     */
    public function taxProfiles(): HasMany
    {
        return $this->ownedTaxProfiles();
    }

    /**
     * Get the current active tax profile for this user.
     *
     * ADR-004: Uses owner-based lookup.
     */
    public function currentTaxProfile(): ?UserTaxProfile
    {
        return UserTaxProfile::getValidForOwnerAt($this->id, now());
    }

    /**
     * Get tax profile valid at a specific date.
     *
     * ADR-004: Uses owner-based lookup.
     */
    public function taxProfileAt(\Carbon\Carbon $date): ?UserTaxProfile
    {
        return UserTaxProfile::getValidForOwnerAt($this->id, $date);
    }

    /**
     * Update tax profile for this user (creates new record, closes previous).
     *
     * ADR-004: Uses owner_user_id.
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

        // Create new profile (ADR-004: owner_user_id)
        return UserTaxProfile::create([
            'owner_user_id' => $this->id,
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
    // USER PREFERENCES (ADR-005)
    // ========================================

    /**
     * Get the user's preferences.
     *
     * @return HasOne<UserPreference, $this>
     */
    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get or create preferences for this user.
     */
    public function getPreferences(): UserPreference
    {
        return UserPreference::forUser($this);
    }

    /**
     * Get the user's preferred theme.
     */
    public function preferredTheme(): string
    {
        return $this->preferences?->theme ?? UserPreference::DEFAULT_THEME;
    }

    // ========================================
    // DEPARTMENT ACCESS (ADR-004)
    // ========================================

    /**
     * Get all department access records for this user.
     *
     * @return HasMany<UserDepartmentAccess, $this>
     */
    public function departmentAccess(): HasMany
    {
        return $this->hasMany(UserDepartmentAccess::class);
    }

    /**
     * Get access level for a specific department.
     */
    public function getAccessForDepartment(int $departmentId): ?UserDepartmentAccess
    {
        return $this->departmentAccess()
            ->where('department_id', $departmentId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if user has any access to a department.
     */
    public function hasAccessToDepartment(int $departmentId): bool
    {
        $access = $this->getAccessForDepartment($departmentId);

        return $access !== null && $access->access_level->value < 3; // Not NONE
    }

    /**
     * Check if user can write in a department.
     */
    public function canWriteInDepartment(int $departmentId): bool
    {
        $access = $this->getAccessForDepartment($departmentId);

        return $access !== null && $access->canWrite();
    }

    /**
     * Check if user can escalate in a department.
     */
    public function canEscalateInDepartment(int $departmentId): bool
    {
        $access = $this->getAccessForDepartment($departmentId);

        return $access !== null && $access->canEscalate();
    }

    // ========================================
    // ADMIN ACCESS CONTROL
    // ========================================

    /**
     * Check if user is allowed to access admin area.
     */
    public function isAdmin(): bool
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
