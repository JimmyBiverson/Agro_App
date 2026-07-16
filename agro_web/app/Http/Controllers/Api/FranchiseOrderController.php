<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FranchiseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Order::forFranchise($user->franchise_id)->with('items.product');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use ($request, $user) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'franchise_id' => $user->franchise_id,
                'ordered_by' => $user->id,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];
                $unitPrice = $product->getBestPrice($quantity);
                $subtotal = $quantity * $unitPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'original_unit_price' => $product->standard_price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order;
        });

        $result->load('items.product');

        ActivityLogger::orderPlaced($result);

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

        $order->load(['items.product', 'stockReceipt.items.product']);
        return response()->json(['data' => $order]);
    }
}
