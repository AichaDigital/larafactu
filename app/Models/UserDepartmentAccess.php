<?php

declare(strict_types=1);

namespace App\Models;

use AichaDigital\Laratickets\Models\Department;
use App\Enums\AccessLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Staff department access model.
 *
 * Controls what access level a staff user has in each department.
 *
 * @property int $id
 * @property int $user_id
 * @property int $department_id
 * @property AccessLevel $access_level
 * @property int|null $granted_by
 * @property \Illuminate\Support\Carbon $granted_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @see ADR-004 for authorization architecture
 */
class UserDepartmentAccess extends Model
{
    protected $table = 'user_department_access';

    protected $fillable = [
        'user_id',
        'department_id',
        'access_level',
        'granted_by',
        'granted_at',
        'expires_at',
    ];

    protected $casts = [
        'access_level' => AccessLevel::class,
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, UserDepartmentAccess>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, UserDepartmentAccess>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<User, UserDepartmentAccess>
     */
    public function grantedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

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
     * Check if user can write in this department.
     */
    public function canWrite(): bool
    {
        return ! $this->isExpired() && $this->access_level->canWrite();
    }

    /**
     * Check if user can delete in this department.
     */
    public function canDelete(): bool
    {
        return ! $this->isExpired() && $this->access_level->canDelete();
    }

    /**
     * Check if user can escalate in this department.
     */
    public function canEscalate(): bool
    {
        return ! $this->isExpired() && $this->access_level->canEscalate();
    }
}
