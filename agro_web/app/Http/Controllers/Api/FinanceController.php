<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Order;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function pendingPayments(): JsonResponse
    {
        $payments = PaymentSubmission::where('status', 'pending')
            ->with(['franchise', 'orders:id,order_number,total_amount,tax_amount,status,delivery_status'])
            ->latest('submitted_at')
            ->paginate(20);

        return response()->json($payments);
    }

    public function showPayment(PaymentSubmission $paymentSubmission): JsonResponse
    {
        $paymentSubmission->load([
            'franchise',
            'orders' => fn ($q) => $q->with('items.product.category'),
            'verifier:id,name',
            'acceptor:id,name',
            'rejector:id,name',
            'infoRequestedBy:id,name',
        ]);

        return response()->json(['data' => $paymentSubmission]);
    }

    public function verify(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        $request->validate([
            'verified_amount' => ['required', 'numeric', 'min:0', 'max:'.$paymentSubmission->amount],
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

        ActivityLogger::paymentVerified($paymentSubmission, $request->user()->id);

        $franchiseUser = User::where('franchise_id', $paymentSubmission->franchise_id)
            ->whereHas('role', fn ($q) => $q->where('name', 'Franchise Partner'))
            ->pluck('id')
            ->all();

        NotificationService::send(
            $franchiseUser,
            'Payment Verified — '.$paymentSubmission->payment_number,
            'Your payment of '.number_format((float) $paymentSubmission->verified_amount, 0).' UGX was verified and is pending final acceptance.',
            'payment_verified',
            PaymentSubmission::class,
            $paymentSubmission->id,
            'franchise/payments/detail',
        );

        return response()->json(['message' => 'Payment verified.', 'data' => $paymentSubmission->fresh(['franchise', 'orders'])]);
    }

    public function accept(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        if ($paymentSubmission->status !== 'verified') {
            return response()->json(['message' => 'Payment must be verified first.'], 422);
        }

        [$paymentSubmission, $franchise, $linkedOrders] = DB::transaction(function () use ($request, $paymentSubmission) {
            $payment = PaymentSubmission::whereKey($paymentSubmission->id)->lockForUpdate()->firstOrFail();
            if ($payment->status !== 'verified') {
                abort(422, 'Payment must be verified first.');
            }
            $payment->update([
                'status' => 'accepted',
                'accepted_by' => $request->user()->id,
                'accepted_at' => now(),
            ]);

            $franchise = Franchise::whereKey($payment->franchise_id)->lockForUpdate()->firstOrFail();
            $franchise->account_balance -= $payment->verified_amount;
            $franchise->save();

            $linkedOrders = $payment->orders()->lockForUpdate()->get();
            foreach ($linkedOrders as $order) {
                $order->update([
                    'delivery_status' => 'ready_for_delivery',
                    'status' => 'approved',
                    'finance_verified_by' => $request->user()->id,
                    'finance_verified_at' => now(),
                ]);
            }

            return [$payment->fresh(['franchise', 'orders']), $franchise->fresh(), $linkedOrders];
        });

        ActivityLogger::paymentAccepted($paymentSubmission, $request->user()->id);

        // Advance any linked orders into the Ready-for-Delivery stage.
        foreach ($linkedOrders as $order) {
            event(new OrderStatusChanged($order, 'approved', 'ready_for_delivery'));

            $franchiseUser = User::where('franchise_id', $order->franchise_id)
                ->whereHas('role', fn ($q) => $q->where('name', 'Franchise Partner'))
                ->pluck('id')
                ->all();

            NotificationService::send(
                $franchiseUser,
                'Ready for Delivery — '.$order->order_number,
                'Your payment was accepted. Order '.$order->order_number.' is ready for delivery. Please review the delivery details.',
                'delivery',
                Order::class,
                $order->id,
                'franchise/orders/detail',
            );
        }

        $staffIds = User::staffOnly()->pluck('id')->all();
        if ($linkedOrders->isNotEmpty()) {
            NotificationService::send(
                $staffIds,
                'Payment Verified — Ready for Delivery',
                'Payment '.$paymentSubmission->payment_number.' was accepted. Orders are ready for delivery verification.',
                'payment_verified',
                PaymentSubmission::class,
                $paymentSubmission->id,
                'staff/orders/detail',
            );
        }

        return response()->json([
            'message' => 'Payment accepted. Franchise balance updated and orders marked ready for delivery.',
            'data' => [
                'payment' => $paymentSubmission->fresh(['franchise', 'orders']),
                'new_balance' => $franchise->account_balance,
            ],
        ]);
    }

    public function reject(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);

        if (! in_array($paymentSubmission->status, ['pending', 'verified'])) {
            return response()->json(['message' => 'Payment cannot be rejected in its current status.'], 422);
        }

        $paymentSubmission->update([
            'status' => 'rejected',
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        ActivityLogger::paymentRejected($paymentSubmission, $request->user()->id, $request->rejection_reason);

        $franchiseUser = User::where('franchise_id', $paymentSubmission->franchise_id)
            ->whereHas('role', fn ($q) => $q->where('name', 'Franchise Partner'))
            ->pluck('id')
            ->all();

        NotificationService::send(
            $franchiseUser,
            'Payment Rejected — '.$paymentSubmission->payment_number,
            'Your payment was rejected: '.$request->rejection_reason.'. Please review and re-submit.',
            'payment_rejected',
            PaymentSubmission::class,
            $paymentSubmission->id,
            'franchise/payments/detail',
        );

        return response()->json(['message' => 'Payment rejected.', 'data' => $paymentSubmission->fresh('franchise')]);
    }

    public function requestInfo(Request $request, PaymentSubmission $paymentSubmission): JsonResponse
    {
        $request->validate(['info_request_note' => 'required|string']);

        if (! in_array($paymentSubmission->status, ['pending', 'verified'])) {
            return response()->json(['message' => 'Payment cannot be requested for information in its current status.'], 422);
        }

        $paymentSubmission->update([
            'status' => 'info_requested',
            'info_requested_by' => $request->user()->id,
            'info_requested_at' => now(),
            'info_request_note' => $request->info_request_note,
        ]);

        $franchiseUser = User::where('franchise_id', $paymentSubmission->franchise_id)
            ->whereHas('role', fn ($q) => $q->where('name', 'Franchise Partner'))
            ->pluck('id')
            ->all();

        NotificationService::send(
            $franchiseUser,
            'More Info Needed — '.$paymentSubmission->payment_number,
            'Finance requested more information for your payment: '.$request->info_request_note,
            'payment',
            PaymentSubmission::class,
            $paymentSubmission->id,
            'franchise/payments/detail',
        );

        return response()->json([
            'message' => 'Information requested from the franchise partner.',
            'data' => $paymentSubmission->fresh('franchise'),
        ]);
    }

    public function allPayments(Request $request): JsonResponse
    {
        $query = PaymentSubmission::with(['franchise', 'orders:id,order_number']);

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
