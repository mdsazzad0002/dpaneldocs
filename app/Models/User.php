<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Permissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) $this->role?->grants($permission);
    }

    /**
     * Staff are users with a role; only they can open the admin panel.
     */
    public function isStaff(): bool
    {
        return $this->role !== null;
    }

    /**
     * Every permission key mapped to whether this user has it, for the UI.
     *
     * @return array<string, bool>
     */
    public function permissionMap(): array
    {
        return collect(Permissions::all())->mapWithKeys(fn ($permission) => [$permission => $this->hasPermission($permission)])->all();
    }
}
