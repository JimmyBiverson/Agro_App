<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockReceiptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $receipts = StockReceipt::where('franchise_id', $user->franchise_id)
            ->with(['order', 'items.product'])
            ->latest()
            ->paginate(20);

        return response()->json($receipts);
    }

    public function show(StockReceipt $stockReceipt): JsonResponse
    {
        $user = request()->user();

        if ($stockReceipt->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $stockReceipt->load(['order', 'items.product', 'receiver']);
        return response()->json(['data' => $stockReceipt]);
    }

    public function confirm(Request $request, StockReceipt $stockReceipt): JsonResponse
    {
        $user = $request->user();

        if ($stockReceipt->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($stockReceipt->status !== 'pending') {
            return response()->json(['message' => 'This receipt has already been processed.'], 422);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.stock_receipt_item_id' => 'required|exists:stock_receipt_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.discrepancy_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        foreach ($request->items as $item) {
            $stockReceiptItem = $stockReceipt->items()->find($item['stock_receipt_item_id']);
            if ($stockReceiptItem) {
                $stockReceiptItem->update([
                    'received_quantity' => $item['received_quantity'],
                    'discrepancy_notes' => $item['discrepancy_notes'] ?? null,
                ]);
            }
        }

        $hasDiscrepancy = $stockReceipt->items->contains(function ($item) {
            return $item->ordered_quantity != $item->received_quantity;
        });

        $stockReceipt->update([
            'status' => 'confirmed',
            'received_by' => $user->id,
            'received_at' => now(),
            'notes' => $request->notes,
            'discrepancy_notes' => $hasDiscrepancy ? 'Discrepancy noted in received items' : null,
        ]);

        foreach ($stockReceipt->items as $item) {
            $franchiseInventory = \App\Models\FranchiseInventory::firstOrNew([
                'franchise_id' => $user->franchise_id,
                'product_id' => $item->product_id,
            ]);

            if ($franchiseInventory->exists) {
                $franchiseInventory->quantity += $item->received_quantity;
            } else {
                $franchiseInventory->quantity = $item->received_quantity;
            }

            $franchiseInventory->total_value = $franchiseInventory->quantity * $item->product->standard_price;
            $franchiseInventory->save();
        }

        $stockReceipt->order->update(['status' => 'delivered']);

        return response()->json([
            'message' => 'Stock receipt confirmed. Inventory updated.',
            'data' => $stockReceipt->fresh(['items.product']),
        ]);
    }
}
