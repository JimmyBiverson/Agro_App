<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'branch_id',
        'franchise_id',
        'pin_code',
        'is_active',
        'phone',
        'status',
        'avatar',
        'last_login_at',
        'employee_id',
        'address',
        'gender',
        'date_of_birth',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'System Administrator';
    }

    public function isStaff(): bool
    {
        return $this->role?->name === 'Farmmantra Staff';
    }

    public function isFinance(): bool
    {
        return $this->role?->name === 'Finance Department';
    }

    public function isFranchisePartner(): bool
    {
        return $this->role?->name === 'Franchise Partner';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->employee_id) {
            return "{$this->name} (#{$this->employee_id})";
        }
        return $this->name;
    }

    public function getFranchiseNameAttribute(): string
    {
        return $this->franchise?->name ?? 'N/A';
    }
}
