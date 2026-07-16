<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_id', 'name', 'customer_code', 'phone',
        'email', 'address', 'city', 'tax_id',
        'opening_balance', 'credit_balance', 'is_wholesale',
        'balance', 'loyalty_points', 'branch_id', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_wholesale' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function scopeForFranchise($query, $franchiseId)
    {
        return $query->where('franchise_id', $franchiseId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
