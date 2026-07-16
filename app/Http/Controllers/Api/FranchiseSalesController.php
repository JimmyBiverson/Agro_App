<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FranchiseInventory;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FranchiseSalesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Sale::where('franchise_id', $user->franchise_id)
            ->with(['customer', 'items.product'])
            ->latest();

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('sale_date', [$request->from_date, $request->to_date]);
        }

        $sales = $query->paginate(20);
        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,mobile_money,bank_transfer,credit',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use ($request, $user) {
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $inventory = FranchiseInventory::where('franchise_id', $user->franchise_id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$item['product_id']}.quantity" => 'Insufficient stock.',
                    ]);
                }

                $totalAmount += $item['quantity'] * $inventory->product->standard_price;
            }

            $discount = $request->discount ?? 0;
            $finalAmount = $totalAmount - $discount;

            $sale = Sale::create([
                'sale_number' => Sale::generateSaleNumber(),
                'franchise_id' => $user->franchise_id,
                'customer_id' => $request->customer_id,
                'created_by' => $user->id,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'credit' ? 'pending' : 'paid',
                'notes' => $request->notes,
                'sale_date' => now()->toDateString(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $subtotal = $item['quantity'] * $product->standard_price;

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->standard_price,
                    'subtotal' => $subtotal,
                ]);

                $inventory = FranchiseInventory::where('franchise_id', $user->franchise_id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                $inventory->quantity -= $item['quantity'];
                $inventory->total_value = $inventory->quantity * $product->standard_price;
                $inventory->save();
            }

            return $sale;
        });

        $result->load(['customer', 'items.product']);

        return response()->json([
            'message' => 'Sale recorded successfully.',
            'data' => $result,
        ], 201);
    }

    public function show(Sale $sale): JsonResponse
    {
        $user = request()->user();

        if ($sale->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $sale->load(['customer', 'items.product', 'creator']);
        return response()->json(['data' => $sale]);
    }
}
