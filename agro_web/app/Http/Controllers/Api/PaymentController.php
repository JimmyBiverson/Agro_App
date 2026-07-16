<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PaymentSubmission::where('franchise_id', $user->franchise_id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest('submitted_at')->paginate(20);
        return response()->json($payments);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:bank_transfer,mobile_money,cash,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'proof_of_payment' => 'required|file|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')->store('payment-proofs', 'public');
        }

        $payment = PaymentSubmission::create([
            'payment_number' => PaymentSubmission::generatePaymentNumber(),
            'franchise_id' => $user->franchise_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_reference' => $request->transaction_reference,
            'bank_name' => $request->bank_name,
            'proof_of_payment_path' => $proofPath,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        ActivityLogger::paymentSubmitted($payment);

        return response()->json(['message' => 'Payment submitted successfully.', 'data' => $payment], 201);
    }

    public function show(PaymentSubmission $paymentSubmission): JsonResponse
    {
        $user = request()->user();

        if ($paymentSubmission->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(['data' => $paymentSubmission]);
    }
}
