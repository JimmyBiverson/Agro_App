<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number', 'franchise_id', 'amount',
        'payment_method', 'transaction_reference', 'bank_name',
        'proof_of_payment_path', 'status', 'submitted_at',
        'verified_by', 'verified_at', 'accepted_by', 'accepted_at',
        'rejection_reason', 'finance_notes', 'verified_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function acceptor()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . date('Ym');
        $last = self::where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $sequence = intval(substr($last->payment_number, -4)) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
