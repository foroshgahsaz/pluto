<?php

namespace App\Models;

use App\Support\MobileNormalizer;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPasswordContract, FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Abilities reserved for the technical developer (is_super_admin).
     *
     * @return list<string>
     */
    public static function developerOnlyPermissions(): array
    {
        return [
            'backups.manage',
        ];
    }

    protected $fillable = [
        'name',
        'mobile',
        'mobile_verified_at',
        'email',
        'email_verified_at',
        'password',
        'is_super_admin',
        'is_platform_admin',
        'is_active',
        'is_blocked',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_platform_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->mobile) {
                $user->mobile = MobileNormalizer::normalize($user->mobile);
            }
        });
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && ! $this->is_blocked;
    }

    /**
     * Technical developer / system operator (highest privilege).
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Alias: technical developer account.
     */
    public function isDeveloper(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Content platform chief — all sites, highest non-developer access.
     */
    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    /**
     * Can operate across every site (developer or platform chief).
     */
    public function isGlobalOperator(): bool
    {
        return $this->isSuperAdmin() || $this->isPlatformAdmin();
    }

    public function belongsToSite(int $siteId): bool
    {
        if ($this->isGlobalOperator()) {
            return true;
        }

        return $this->sites()->where('sites.id', $siteId)->exists();
    }

    public function authorizedSiteIds(): array
    {
        if ($this->isGlobalOperator()) {
            return Site::query()->pluck('id')->all();
        }

        return $this->sites()->pluck('sites.id')->all();
    }

    public function setPermissionsTeamIdForSite(?int $siteId): void
    {
        setPermissionsTeamId($siteId);
    }

    public function hasSitePermission(string $permission, int $siteId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isPlatformAdmin()) {
            return ! in_array($permission, self::developerOnlyPermissions(), true);
        }

        if (! $this->belongsToSite($siteId)) {
            return false;
        }

        $previous = getPermissionsTeamId();
        setPermissionsTeamId($siteId);

        try {
            return $this->checkPermissionTo($permission);
        } finally {
            setPermissionsTeamId($previous);
        }
    }
}
