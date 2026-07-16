<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'franchise_id', 'ordered_by', 'status',
        'received_at', 'served_at', 'completed_at', 'notes',
        'expected_delivery_date', 'approved_by', 'approved_at',
        'decline_reason', 'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expected_delivery_date' => 'datetime',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function orderedByUser()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockReceipt()
    {
        return $this->hasOne(StockReceipt::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForFranchise($query, $franchiseId)
    {
        return $query->where('franchise_id', $franchiseId);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ym');
        $last = self::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $sequence = intval(substr($last->order_number, -4)) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
