<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FranchiseInventory;
use App\Models\StockMovement;
use App\Models\StockReceipt;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReceiptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $receipts = StockReceipt::where('franchise_id', $user->franchise_id)
            ->with(['order', 'items.product'])
            ->latest()
            ->get();

        return response()->json(['data' => $receipts]);
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

        $submittedItems = collect($request->input('items'))->keyBy('stock_receipt_item_id');
        $receiptItemIds = $stockReceipt->items()->pluck('id')->map(fn ($id) => (string) $id);
        if ($submittedItems->count() !== $receiptItemIds->count() || $receiptItemIds->diff($submittedItems->keys()->map(fn ($id) => (string) $id))->isNotEmpty()) {
            return response()->json(['message' => 'Every receipt item must be explicitly reconciled.'], 422);
        }

        $stockReceipt = DB::transaction(function () use ($request, $stockReceipt, $user, $submittedItems) {
            $lockedReceipt = StockReceipt::whereKey($stockReceipt->id)->lockForUpdate()->firstOrFail();
            $items = $lockedReceipt->items()->with('product')->lockForUpdate()->get();

            foreach ($items as $item) {
                $input = $submittedItems->get($item->id);
                $item->update([
                    'received_quantity' => $input['received_quantity'],
                    'discrepancy_notes' => $input['discrepancy_notes'] ?? null,
                ]);
            }

            $hasDiscrepancy = $items->contains(fn ($item) => $item->ordered_quantity != $item->received_quantity);
            $lockedReceipt->update([
                'status' => 'confirmed',
                'received_by' => $user->id,
                'received_at' => now(),
                'notes' => $request->notes,
                'discrepancy_notes' => $hasDiscrepancy ? 'Discrepancy noted in received items' : null,
            ]);

            foreach ($items as $item) {
                $franchiseInventory = FranchiseInventory::where('franchise_id', $user->franchise_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();
                if (! $franchiseInventory) {
                    $franchiseInventory = new FranchiseInventory([
                        'franchise_id' => $user->franchise_id,
                        'product_id' => $item->product_id,
                        'quantity' => 0,
                    ]);
                }
                $franchiseInventory->quantity += $item->received_quantity;
                $franchiseInventory->total_value = $franchiseInventory->quantity * $item->product->standard_price;
                $franchiseInventory->save();

                if ($item->received_quantity > 0) {
                    StockMovement::log('franchise_in', $item->product_id, $item->received_quantity, $item->product->standard_price, StockReceipt::class, $lockedReceipt->id, "Receipt {$lockedReceipt->receipt_number} confirmed", $user->id);
                }
            }

            $order = $lockedReceipt->order()->lockForUpdate()->firstOrFail();
            $order->update(['status' => 'delivered']);
            $franchise = $user->franchise()->lockForUpdate()->first();
            if ($franchise) {
                $franchise->account_balance += $order->total_amount;
                $franchise->save();
            }

            return $lockedReceipt->fresh(['items.product']);
        });

        ActivityLogger::orderDelivered($stockReceipt->order, $user->id);

        return response()->json([
            'message' => 'Stock receipt confirmed. Inventory updated.',
            'data' => $stockReceipt->fresh(['items.product']),
        ]);
    }
}
