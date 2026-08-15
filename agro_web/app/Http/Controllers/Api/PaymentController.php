<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PaymentSubmission::with('orders:id,order_number,total_amount,tax_amount,status,delivery_status')
            ->where('franchise_id', $user->franchise_id);

        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest('submitted_at')->get();

        return response()->json(['data' => $payments]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'order_id' => 'nullable|exists:orders,id',
            'order_ids' => 'nullable|array',
            'order_ids.*' => 'exists:orders,id',
            'payment_method' => 'required|string|in:bank_transfer,mobile_money,cash,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'proof_of_payment' => 'nullable|file|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $orderIds = $request->filled('order_ids')
            ? (array) $request->order_ids
            : ($request->filled('order_id') ? [$request->order_id] : []);

        // Validate that all linked orders belong to this franchise and are approved.
        if (! empty($orderIds)) {
            $orders = Order::whereIn('id', $orderIds)
                ->where('franchise_id', $user->franchise_id)
                ->where('status', 'approved')
                ->pluck('id')
                ->all();

            if (count($orders) !== count(array_unique($orderIds))) {
                return response()->json([
                    'message' => 'One or more orders are invalid, not approved, or do not belong to your franchise.',
                ], 422);
            }
        }

        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')->store('payment-proofs', 'public');
        }

        $payment = PaymentSubmission::create([
            'payment_number' => PaymentSubmission::generatePaymentNumber(),
            'franchise_id' => $user->franchise_id,
            'order_id' => $orderIds[0] ?? null,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_reference' => $request->transaction_reference,
            'bank_name' => $request->bank_name,
            'proof_of_payment_path' => $proofPath,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        if (! empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $payment->orders()->attach($orderId, ['allocated_amount' => 0]);
            }
        }

        ActivityLogger::paymentSubmitted($payment);

        $financeIds = User::whereHas('role', fn ($q) => $q->where('name', 'Finance Department'))
            ->pluck('id')
            ->all();

        NotificationService::send(
            $financeIds,
            'New Payment '.$payment->payment_number,
            "{$user->franchise?->name} submitted a payment of ".number_format((float) $request->amount, 0).' UGX.',
            'payment',
            PaymentSubmission::class,
            $payment->id,
            'finance/payments/detail',
        );

        return response()->json(['message' => 'Payment submitted successfully.', 'data' => $payment->load('orders')], 201);
    }

    public function show(PaymentSubmission $paymentSubmission): JsonResponse
    {
        $user = request()->user();

        if ($paymentSubmission->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $paymentSubmission->load([
            'orders:id,order_number,total_amount,tax_amount,status,delivery_status',
            'franchise:id,name,code',
        ]);

        return response()->json(['data' => $paymentSubmission]);
    }

    public function uploadProof(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        $user = request()->user();

        if ($paymentSubmission->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'proof_of_payment' => 'required|file|image|max:5120',
        ]);

        $proofPath = $request->file('proof_of_payment')->store('payment-proofs', 'public');

        // Allow a rejected / info-requested payment to be re-submitted for review.
        $newStatus = $paymentSubmission->status === 'pending' ? 'pending' : 'pending';

        $paymentSubmission->update([
            'proof_of_payment_path' => $proofPath,
            'status' => $newStatus,
            'rejection_reason' => null,
            'info_request_note' => null,
        ]);

        $financeIds = User::whereHas('role', fn ($q) => $q->where('name', 'Finance Department'))
            ->pluck('id')
            ->all();

        NotificationService::send(
            $financeIds,
            'Payment Re-submitted — '.$paymentSubmission->payment_number,
            "{$user->franchise?->name} uploaded updated proof for {$paymentSubmission->payment_number}.",
            'payment',
            PaymentSubmission::class,
            $paymentSubmission->id,
            'finance/payments/detail',
        );

        return response()->json([
            'message' => 'Proof of payment uploaded. Payment is pending review again.',
            'data' => $paymentSubmission->fresh(),
        ]);
    }
}
