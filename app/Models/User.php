<?php

namespace App\Models;

use App\Support\MobileNormalizer;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'mobile',
        'mobile_verified_at',
        'email',
        'email_verified_at',
        'password',
        'is_super_admin',
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

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function belongsToSite(int $siteId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->sites()->where('sites.id', $siteId)->exists();
    }

    public function authorizedSiteIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Site::query()->pluck('id')->all();
        }

        return $this->sites()->pluck('sites.id')->all();
    }

    public function setPermissionsTeamIdForSite(?int $siteId): void
    {
        setPermissionsTeamId($siteId);
    }
}
