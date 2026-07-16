<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FranchiseInventory;
use App\Models\WarehouseInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffInventoryController extends Controller
{
    public function warehouseStock(): JsonResponse
    {
        $stock = WarehouseInventory::with('product.category')->latest()->paginate(50);
        return response()->json($stock);
    }

    public function franchiseStock(): JsonResponse
    {
        $stock = FranchiseInventory::with(['franchise', 'product.category'])->latest()->paginate(50);
        return response()->json($stock);
    }

    public function updateWarehouseStock(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $inventory = WarehouseInventory::updateOrCreate(
            ['product_id' => $request->product_id],
            [
                'quantity' => $request->quantity,
                'reorder_level' => $request->reorder_level ?? 0,
                'last_restocked_at' => now(),
            ]
        );

        $inventory->load('product');

        return response()->json(['message' => 'Warehouse stock updated.', 'data' => $inventory]);
    }
}
