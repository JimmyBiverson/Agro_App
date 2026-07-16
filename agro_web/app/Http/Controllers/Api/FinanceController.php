<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\PaymentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function pendingPayments(): JsonResponse
    {
        $payments = PaymentSubmission::where('status', 'pending')
            ->with('franchise')
            ->latest('submitted_at')
            ->paginate(20);

        return response()->json($payments);
    }

    public function showPayment(PaymentSubmission $paymentSubmission): JsonResponse
    {
        $paymentSubmission->load('franchise');
        return response()->json(['data' => $paymentSubmission]);
    }

    public function verify(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        $request->validate([
            'verified_amount' => 'required|numeric|min:0',
            'finance_notes' => 'nullable|string',
        ]);

        if ($paymentSubmission->status !== 'pending') {
            return response()->json(['message' => 'Payment is not pending.'], 422);
        }

        $paymentSubmission->update([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'verified_amount' => $request->verified_amount,
            'finance_notes' => $request->finance_notes,
        ]);

        return response()->json(['message' => 'Payment verified.', 'data' => $paymentSubmission->fresh('franchise')]);
    }

    public function accept(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        if ($paymentSubmission->status !== 'verified') {
            return response()->json(['message' => 'Payment must be verified first.'], 422);
        }

        $paymentSubmission->update([
            'status' => 'accepted',
            'accepted_by' => $request->user()->id,
            'accepted_at' => now(),
        ]);

        $franchise = Franchise::find($paymentSubmission->franchise_id);
        $franchise->account_balance += $paymentSubmission->verified_amount;
        $franchise->save();

        return response()->json([
            'message' => 'Payment accepted. Franchise balance updated.',
            'data' => [
                'payment' => $paymentSubmission->fresh('franchise'),
                'new_balance' => $franchise->fresh()->account_balance,
            ],
        ]);
    }

    public function reject(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $paymentSubmission->update([
            'status' => 'rejected',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json(['message' => 'Payment rejected.', 'data' => $paymentSubmission->fresh('franchise')]);
    }

    public function allPayments(Request $request): JsonResponse
    {
        $query = PaymentSubmission::with('franchise');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        $payments = $query->latest('submitted_at')->paginate(20);
        return response()->json($payments);
    }
}
