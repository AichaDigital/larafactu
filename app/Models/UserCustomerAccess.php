<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccessLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserCustomerAccess - Delegate access to customer accounts.
 *
 * This model manages permissions for delegates to access customer accounts.
 * A delegate can have different access levels and granular permissions
 * for each customer they are granted access to.
 *
 * @see ADR-004 for authorization architecture
 * @see ADR-006 for consolidated state
 *
 * @property string $user_id FK to users (delegate receiving access)
 * @property string $customer_user_id FK to users (customer being accessed)
 * @property AccessLevel $access_level Access level enum
 * @property bool $can_view_invoices Granular permission
 * @property bool $can_view_services Granular permission
 * @property bool $can_manage_tickets Granular permission
 * @property bool $can_manage_delegates Granular permission
 * @property string|null $granted_by FK to users (who granted access)
 * @property \Carbon\Carbon $granted_at When access was granted
 * @property \Carbon\Carbon|null $expires_at When access expires (null = never)
 * @property-read User $user The delegate user
 * @property-read User $customer The customer user
 * @property-read User|null $grantor The user who granted access
 */
class UserCustomerAccess extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'can_view_invoices' => false,
        'can_view_services' => false,
        'can_manage_tickets' => false,
        'can_manage_delegates' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'customer_user_id',
        'access_level',
        'can_view_invoices',
        'can_view_services',
        'can_manage_tickets',
        'can_manage_delegates',
        'granted_by',
        'granted_at',
        'expires_at',
    ];

    /**
     * The table associated with the model.
     */
    protected $table = 'user_customer_access';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_level' => AccessLevel::class,
            'can_view_invoices' => 'boolean',
            'can_view_services' => 'boolean',
            'can_manage_tickets' => 'boolean',
            'can_manage_delegates' => 'boolean',
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the delegate user who has access.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the customer user being accessed.
     *
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    /**
     * Get the user who granted this access.
     *
     * @return BelongsTo<User, $this>
     */
    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope to filter only active (non-expired) access.
     *
     * @param  Builder<UserCustomerAccess>  $query
     * @return Builder<UserCustomerAccess>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to filter by delegate user.
     *
     * @param  Builder<UserCustomerAccess>  $query
     * @return Builder<UserCustomerAccess>
     */
    public function scopeForDelegate(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by customer.
     *
     * @param  Builder<UserCustomerAccess>  $query
     * @return Builder<UserCustomerAccess>
     */
    public function scopeForCustomer(Builder $query, string $customerId): Builder
    {
        return $query->where('customer_user_id', $customerId);
    }

    // ========================================
    // EXPIRATION METHODS
    // ========================================

    /**
     * Check if access has expired.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if access is currently active (not expired).
     */
    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    // ========================================
    // ACCESS LEVEL METHODS
    // ========================================

    /**
     * Check if access level allows reading.
     */
    public function canRead(): bool
    {
        return $this->access_level->canRead();
    }

    /**
     * Check if access level allows writing.
     */
    public function canWrite(): bool
    {
        return $this->access_level->canWrite();
    }

    /**
     * Check if access level allows deleting.
     */
    public function canDelete(): bool
    {
        return $this->access_level->canDelete();
    }

    // ========================================
    // GRANULAR PERMISSION METHODS
    // ========================================

    /**
     * Check if delegate can view invoices.
     * Returns false if access is expired.
     */
    public function canViewInvoices(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->can_view_invoices;
    }

    /**
     * Check if delegate can view services.
     * Returns false if access is expired.
     */
    public function canViewServices(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->can_view_services;
    }

    /**
     * Check if delegate can manage tickets.
     * Returns false if access is expired.
     */
    public function canManageTickets(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->can_manage_tickets;
    }

    /**
     * Check if delegate can manage other delegates.
     * Returns false if access is expired.
     */
    public function canManageDelegates(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->can_manage_delegates;
    }
}
