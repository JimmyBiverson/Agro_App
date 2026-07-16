<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StockReceipt;
use App\Models\WarehouseInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['franchise', 'items.product', 'orderedByUser']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }

        $orders = $query->latest()->paginate(20);
        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['franchise', 'items.product', 'orderedByUser', 'stockReceipt']);
        return response()->json(['data' => $order]);
    }

    public function approve(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'expected_delivery_date' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order is not pending.'], 422);
        }

        foreach ($order->items as $item) {
            $warehouse = WarehouseInventory::where('product_id', $item->product_id)->first();
            if (!$warehouse || $warehouse->quantity < $item->quantity) {
                return response()->json([
                    'message' => "Insufficient warehouse stock for {$item->product->name}.",
                ], 422);
            }
        }

        $result = DB::transaction(function () use ($request, $order) {
            foreach ($order->items as $item) {
                $warehouse = WarehouseInventory::where('product_id', $item->product_id)->first();
                $warehouse->quantity -= $item->quantity;
                $warehouse->save();
            }

            $order->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes ?? $order->notes,
            ]);

            $stockReceipt = StockReceipt::create([
                'receipt_number' => StockReceipt::generateReceiptNumber(),
                'order_id' => $order->id,
                'franchise_id' => $order->franchise_id,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($order->items as $item) {
                $stockReceipt->items()->create([
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'ordered_quantity' => $item->quantity,
                    'received_quantity' => 0,
                ]);
            }

            return $order;
        });

        $result->load(['franchise', 'items.product', 'stockReceipt.items.product']);

        return response()->json([
            'message' => 'Order approved. Warehouse stock reserved and stock receipt created.',
            'data' => $result,
        ]);
    }

    public function decline(Request $request, Order $order): JsonResponse
    {
        $request->validate(['decline_reason' => 'required|string']);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order is not pending.'], 422);
        }

        $order->update([
            'status' => 'declined',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'decline_reason' => $request->decline_reason,
        ]);

        return response()->json(['message' => 'Order declined.', 'data' => $order->fresh()]);
    }

    public function adjust(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.adjusted_quantity' => 'required|integer|min:0',
            'items.*.adjustment_notes' => 'nullable|string',
        ]);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order is not pending.'], 422);
        }

        $newTotal = 0;

        foreach ($request->items as $item) {
            $orderItem = $order->items()->find($item['order_item_id']);
            if ($orderItem) {
                $orderItem->update([
                    'adjusted_quantity' => $item['adjusted_quantity'],
                    'adjustment_notes' => $item['adjustment_notes'] ?? null,
                ]);
                $newTotal += $item['adjusted_quantity'] * $orderItem->unit_price;
            }
        }

        $order->update(['total_amount' => $newTotal]);

        return response()->json(['message' => 'Order items adjusted.', 'data' => $order->fresh(['items.product'])]);
    }
}
