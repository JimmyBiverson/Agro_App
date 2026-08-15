<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FranchiseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Order::forFranchise($user->franchise_id)
            ->with('items.product.category')
            ->withCount(['acceptedPayments as payment_accepted_count']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use ($request, $user) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'franchise_id' => $user->franchise_id,
                'ordered_by' => $user->id,
                'status' => 'pending',
                'delivery_status' => 'pending',
                'notes' => $request->notes,
            ]);

            $totalAmount = 0;
            $totalTax = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (float) $item['quantity'];
                $baseUnitPrice = $product->getBestPrice($quantity);
                $tax = $product->calculateTaxPrice($baseUnitPrice);

                $finalUnitPrice = (float) $tax['final_price'];
                $unitTax = round($finalUnitPrice - $baseUnitPrice, 2);
                $subtotal = round($quantity * $finalUnitPrice, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $finalUnitPrice,
                    'base_unit_price' => $baseUnitPrice,
                    'tax_rate' => $product->tax_enabled ? (float) $product->tax_rate : 0.00,
                    'tax_amount' => round($quantity * $unitTax, 2),
                    'original_unit_price' => $product->standard_price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $totalTax += round($quantity * $unitTax, 2);
            }

            if ($user->franchise && (float) $user->franchise->credit_limit > 0) {
                $creditLimit = (float) $user->franchise->credit_limit;
                $outstanding = (float) $user->franchise->account_balance;

                if (($outstanding + $totalAmount) > $creditLimit) {
                    throw ValidationException::withMessages([
                        'order' => 'This order exceeds the franchise credit limit (limit: '.number_format($creditLimit, 0).', projected outstanding: '.number_format($outstanding + $totalAmount, 0).').',
                    ]);
                }
            }

            $order->update([
                'total_amount' => round($totalAmount, 2),
                'tax_amount' => round($totalTax, 2),
            ]);

            return $order;
        });

        $result->load('items.product.category');

        ActivityLogger::orderPlaced($result);

        // Notify every Farmmantra Staff member about the new order.
        $staffIds = User::staffOnly()->pluck('id')->all();
        NotificationService::send(
            $staffIds,
            'New Order '.$result->order_number,
            "{$user->franchise?->name} placed a new order for ".$result->items->count().' product(s) worth '.number_format((float) $result->total_amount, 0),
            'order',
            Order::class,
            $result->id,
            'staff/orders/detail',
        );

        event(new OrderStatusChanged($result, 'pending', 'pending'));

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => $result,
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $user = request()->user();

        if ($order->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $order->loadCount(['acceptedPayments as payment_accepted_count']);
        $order->load([
            'items.product.category',
            'stockReceipt.items.product',
            'payments' => fn ($q) => $q->where('status', 'accepted')->latest('accepted_at'),
        ]);

        return response()->json(['data' => $order]);
    }

    /**
     * Franchise partner confirms physical receipt of a delivered order.
     */
    public function confirmDelivery(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($order->delivery_status !== 'delivered') {
            return response()->json([
                'message' => 'Order can only be confirmed after it has been delivered.',
            ], 422);
        }

        $order->update([
            'delivery_status' => 'confirmed',
            'status' => 'delivered',
            'received_at' => $order->received_at ?? now(),
            'completed_at' => now(),
        ]);

        ActivityLogger::orderDelivered($order, $user->id);

        $staffIds = User::staffOnly()->pluck('id')->all();
        NotificationService::send(
            $staffIds,
            'Delivery Confirmed — '.$order->order_number,
            "{$user->franchise?->name} confirmed receipt of order {$order->order_number}.",
            'delivery',
            Order::class,
            $order->id,
            'staff/orders/detail',
        );

        event(new OrderStatusChanged($order, 'delivered', 'confirmed'));

        return response()->json([
            'message' => 'Delivery confirmed. Thank you!',
            'data' => $order->fresh(['franchise', 'items.product.category']),
        ]);
    }
}
