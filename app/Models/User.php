<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
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
            'role' => UserRole::class,
        ];
    }

    public function isSuperRole(): bool
    {
        return $this->role === UserRole::SUPER_ROLE;
    }

    public function hasRole(string|UserRole ...$roles): bool
    {
        if ($this->isSuperRole()) {
            return true;
        }

        foreach ($roles as $role) {
            $value = $role instanceof UserRole ? $role->value : $role;
            if ($this->role?->value === $value) {
                return true;
            }
        }

        return false;
    }

    public function canAccessModule(string $module): bool
    {
        if ($this->isSuperRole()) {
            return true;
        }

        return match ($module) {
            'sales' => $this->role === UserRole::SALES,
            'production' => $this->role === UserRole::PRODUCTION,
            'stin' => $this->role === UserRole::STIN,
            'crm' => $this->role === UserRole::ADMIN_CRM,
            'ecommerce' => $this->role === UserRole::ADMIN_ECOMMERCE,
            default => false,
        };
    }
}
