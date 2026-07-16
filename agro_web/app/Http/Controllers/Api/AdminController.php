<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Franchise;
use App\Models\FranchiseInventory;
use App\Models\Order;
use App\Models\PaymentSubmission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\WarehouseInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $now = now();

        $data = [
            'summary' => [
                'total_franchises' => Franchise::count(),
                'active_franchises' => Franchise::where('is_active', true)->count(),
                'total_users' => User::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'pending_payments' => PaymentSubmission::where('status', 'pending')->count(),
                'total_sales_this_month' => Sale::whereMonth('sale_date', $now->month)
                    ->whereYear('sale_date', $now->year)
                    ->sum('final_amount'),
                'total_sales_last_month' => Sale::whereMonth('sale_date', $now->copy()->subMonth()->month)
                    ->whereYear('sale_date', $now->copy()->subMonth()->year)
                    ->sum('final_amount'),
                'total_sales_ytd' => Sale::whereYear('sale_date', $now->year)->sum('final_amount'),
                'total_outstanding' => Franchise::where('account_balance', '>', 0)->sum('account_balance'),
                'low_stock_products' => WarehouseInventory::whereColumn('quantity', '<=', 'reorder_level')->count(),
                'total_inventory_value' => WarehouseInventory::join('products', 'products.id', '=', 'warehouse_inventories.product_id')
                    ->sum(DB::raw('warehouse_inventories.quantity * products.standard_price')),
            ],

            'sales_by_franchise' => Sale::select('franchise_id', DB::raw('SUM(final_amount) as total_sales'), DB::raw('COUNT(*) as sale_count'))
                ->whereMonth('sale_date', $now->month)
                ->whereYear('sale_date', $now->year)
                ->with('franchise:id,name,code')
                ->groupBy('franchise_id')
                ->orderByDesc('total_sales')
                ->get(),

            'sales_trend' => Sale::select(
                    DB::raw('DATE(sale_date) as date'),
                    DB::raw('SUM(final_amount) as total_sales'),
                    DB::raw('COUNT(*) as sale_count')
                )
                ->where('sale_date', '>=', $now->copy()->subDays(30)->startOfDay())
                ->groupBy(DB::raw('DATE(sale_date)'))
                ->orderBy('date')
                ->get(),

            'top_products' => \App\Models\SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                ->with('product:id,name,sku')
                ->groupBy('product_id')
                ->orderByDesc('total_revenue')
                ->limit(10)
                ->get(),

            'order_status_breakdown' => Order::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),

            'recent_orders' => Order::with(['franchise:id,name,code', 'items.product:id,name'])
                ->latest()
                ->limit(10)
                ->get(),

            'franchise_performance' => Franchise::select('franchises.*')
                ->withCount(['orders as total_orders' => function ($q) use ($now) {
                    $q->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                }])
                ->withSum(['orders as total_order_value' => function ($q) use ($now) {
                    $q->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                }], 'total_amount')
                ->withCount(['sales as total_sales_count' => function ($q) use ($now) {
                    $q->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year);
                }])
                ->withSum(['sales as total_sales_value' => function ($q) use ($now) {
                    $q->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year);
                }], 'final_amount')
                ->orderByDesc('total_sales_value')
                ->get(),
        ];

        return response()->json(['data' => $data]);
    }

    public function franchises(): JsonResponse
    {
        $franchises = Franchise::withCount(['users', 'orders', 'sales'])
            ->latest()
            ->paginate(20);

        return response()->json($franchises);
    }

    public function storeFranchise(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:franchises,code',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'region' => 'required|string|max:100',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $franchise = Franchise::create($request->only([
            'name', 'code', 'contact_person', 'phone', 'email',
            'region', 'address', 'credit_limit',
        ]));

        return response()->json(['message' => 'Franchise created.', 'data' => $franchise], 201);
    }

    public function showFranchise(Franchise $franchise): JsonResponse
    {
        $franchise->loadCount(['users', 'orders', 'sales', 'paymentSubmissions']);
        return response()->json(['data' => $franchise]);
    }

    public function updateFranchise(Request $request, Franchise $franchise): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email',
            'region' => 'sometimes|string|max:100',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $franchise->update($request->only([
            'name', 'contact_person', 'phone', 'email',
            'region', 'address', 'credit_limit', 'is_active',
        ]));

        return response()->json(['message' => 'Franchise updated.', 'data' => $franchise->fresh()]);
    }

    public function users(): JsonResponse
    {
        $users = User::with(['role', 'franchise'])->latest()->paginate(20);
        return response()->json($users);
    }

    public function storeUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'franchise_id' => 'nullable|exists:franchises,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            ...$request->only(['name', 'email', 'phone', 'role_id', 'franchise_id', 'is_active']),
            'password' => Hash::make($request->password),
        ]);

        $user->load('role', 'franchise');

        return response()->json(['message' => 'User created.', 'data' => $user], 201);
    }

    public function products(): JsonResponse
    {
        $products = Product::with(['category', 'priceSlabs', 'warehouseInventory'])
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'unit_of_measure' => 'nullable|string|max:50',
            'packaging_details' => 'nullable|string',
            'standard_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($request->only([
            'name', 'sku', 'category_id', 'unit_of_measure',
            'packaging_details', 'standard_price', 'description',
        ]));

        WarehouseInventory::create(['product_id' => $product->id, 'quantity' => 0]);

        return response()->json(['message' => 'Product created.', 'data' => $product->fresh('category')], 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'unit_of_measure' => 'nullable|string|max:50',
            'packaging_details' => 'nullable|string',
            'standard_price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $product->update($request->only([
            'name', 'category_id', 'unit_of_measure',
            'packaging_details', 'standard_price', 'description', 'is_active',
        ]));

        return response()->json(['message' => 'Product updated.', 'data' => $product->fresh('category')]);
    }

    public function storePriceSlab(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1|gte:min_quantity',
            'slab_price' => 'required|numeric|min:0',
        ]);

        $slab = $product->priceSlabs()->create($request->only([
            'min_quantity', 'max_quantity', 'slab_price',
        ]));

        return response()->json(['message' => 'Price slab created.', 'data' => $slab], 201);
    }

    public function storeSalesTarget(Request $request): JsonResponse
    {
        $request->validate([
            'franchise_id' => 'required|exists:franchises,id',
            'product_category_id' => 'nullable|exists:categories,id',
            'target_amount' => 'required|numeric|min:0',
            'target_quantity' => 'nullable|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2024',
        ]);

        $target = \App\Models\SalesTarget::updateOrCreate(
            [
                'franchise_id' => $request->franchise_id,
                'product_category_id' => $request->product_category_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            $request->only(['target_amount', 'target_quantity'])
        );

        return response()->json(['message' => 'Sales target set.', 'data' => $target->fresh(['franchise', 'productCategory'])]);
    }
}
