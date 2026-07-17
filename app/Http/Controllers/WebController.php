<?php

namespace App\Http\Controllers;

use App\Models\Franchise;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSubmission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\WarehouseInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('web.dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if ($user->franchise_id && !$user->franchise?->is_active) {
            return back()->withErrors(['email' => 'Your franchise account has been deactivated.']);
        }

        Auth::login($user, true);
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('web.dashboard'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('web.login');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $role = $user->role?->name;
        $now = now();
        $dashboard = [];

        if ($role === 'System Administrator') {
            $dashboard['summary'] = [
                'total_franchises' => Franchise::count(),
                'active_franchises' => Franchise::where('is_active', true)->count(),
                'total_users' => User::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'pending_payments' => PaymentSubmission::where('status', 'pending')->count(),
                'total_sales_this_month' => Sale::whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year)->sum('final_amount'),
                'total_sales_last_month' => Sale::whereMonth('sale_date', $now->copy()->subMonth()->month)->whereYear('sale_date', $now->copy()->subMonth()->year)->sum('final_amount'),
                'total_sales_ytd' => Sale::whereYear('sale_date', $now->year)->sum('final_amount'),
                'total_outstanding' => Franchise::where('account_balance', '>', 0)->sum('account_balance'),
                'low_stock_products' => WarehouseInventory::whereColumn('quantity', '<=', 'reorder_level')->count(),
                'total_inventory_value' => WarehouseInventory::join('products', 'products.id', '=', 'warehouse_inventories.product_id')
                    ->sum(\Illuminate\Support\Facades\DB::raw('warehouse_inventories.quantity * products.standard_price')),
            ];
            $dashboard['sales_by_franchise'] = Sale::select('franchise_id', \Illuminate\Support\Facades\DB::raw('SUM(final_amount) as total_sales'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as sale_count'))->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year)->with('franchise:id,name,code')->groupBy('franchise_id')->orderByDesc('total_sales')->get();
            $dashboard['sales_trend'] = Sale::select(\Illuminate\Support\Facades\DB::raw('DATE(sale_date) as date'), \Illuminate\Support\Facades\DB::raw('SUM(final_amount) as total_sales'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as sale_count'))->where('sale_date', '>=', $now->copy()->subDays(30)->startOfDay())->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(sale_date)'))->orderBy('date')->get();
            $dashboard['top_products'] = \App\Models\SaleItem::select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'), \Illuminate\Support\Facades\DB::raw('SUM(subtotal) as total_revenue'))->with('product:id,name,sku')->groupBy('product_id')->orderByDesc('total_revenue')->limit(10)->get();
            $dashboard['recent_orders'] = Order::with(['franchise:id,name,code', 'items.product:id,name'])->latest()->limit(10)->get();
            $dashboard['franchise_performance'] = Franchise::select('franchises.*')->withCount(['orders as total_orders' => fn($q) => $q->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)])->withSum(['orders as total_order_value' => fn($q) => $q->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)], 'total_amount')->withCount(['sales as total_sales_count' => fn($q) => $q->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year)])->withSum(['sales as total_sales_value' => fn($q) => $q->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year)], 'final_amount')->orderByDesc('total_sales_value')->get();
            $dashboard['order_status_breakdown'] = Order::select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))->groupBy('status')->get();
        } elseif ($role === 'Farmmantra Staff') {
            $dashboard['summary'] = [
                'pending_orders' => Order::where('status', 'pending')->count(),
                'approved_orders_today' => Order::where('status', 'approved')->whereDate('approved_at', $now->toDateString())->count(),
                'total_orders_this_month' => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
                'declined_orders_this_month' => Order::where('status', 'declined')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
                'total_orders_value_this_month' => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('total_amount'),
                'low_stock_products' => WarehouseInventory::whereColumn('quantity', '<=', 'reorder_level')->count(),
                'total_warehouse_value' => WarehouseInventory::join('products', 'products.id', '=', 'warehouse_inventories.product_id')
                    ->sum(\Illuminate\Support\Facades\DB::raw('warehouse_inventories.quantity * products.standard_price')),
                'active_franchises' => Franchise::where('is_active', true)->count(),
            ];
            $dashboard['pending_orders_list'] = Order::where('status', 'pending')->with(['franchise:id,name,code', 'items.product:id,name,sku'])->latest()->limit(10)->get();
            $dashboard['recent_approved_orders'] = Order::where('status', 'approved')->with(['franchise:id,name,code', 'items.product:id,name'])->latest()->limit(5)->get();
            $dashboard['warehouse_stock'] = WarehouseInventory::with('product:id,name,sku,unit_of_measure')->get();
            $dashboard['orders_by_status'] = Order::select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))->groupBy('status')->get();
            $dashboard['orders_trend'] = Order::select(\Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as order_count'), \Illuminate\Support\Facades\DB::raw('SUM(total_amount) as total_value'))->where('created_at', '>=', $now->copy()->subDays(30)->startOfDay())->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'))->orderBy('date')->get();
        } elseif ($role === 'Finance Department') {
            $dashboard['summary'] = [
                'pending_payments_count' => PaymentSubmission::where('status', 'pending')->count(),
                'pending_payments_total' => PaymentSubmission::where('status', 'pending')->sum('amount'),
                'accepted_this_month' => PaymentSubmission::where('status', 'accepted')->whereMonth('accepted_at', $now->month)->count(),
                'accepted_amount_this_month' => PaymentSubmission::where('status', 'accepted')->whereMonth('accepted_at', $now->month)->sum('verified_amount'),
                'total_outstanding' => Franchise::where('account_balance', '>', 0)->sum('account_balance'),
                'total_collected_ytd' => PaymentSubmission::where('status', 'accepted')->whereYear('accepted_at', $now->year)->sum('verified_amount'),
            ];
            $dashboard['outstanding_by_franchise'] = Franchise::where('account_balance', '>', 0)->select('id', 'name', 'code', 'account_balance', 'credit_limit')->orderByDesc('account_balance')->get()->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'code' => $f->code, 'balance' => $f->account_balance, 'credit_limit' => $f->credit_limit, 'utilization' => $f->credit_limit > 0 ? round(($f->account_balance / $f->credit_limit) * 100, 1) : 0]);
            $dashboard['recent_pending'] = PaymentSubmission::where('status', 'pending')->with('franchise:id,name,code')->latest('submitted_at')->limit(10)->get();
        } elseif ($role === 'Franchise Partner') {
            $franchiseId = $user->franchise_id;
            $dashboard['summary'] = [
                'total_sales_this_month' => Sale::where('franchise_id', $franchiseId)->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year)->sum('final_amount'),
                'total_sales_ytd' => Sale::where('franchise_id', $franchiseId)->whereYear('sale_date', $now->year)->sum('final_amount'),
                'total_inventory_value' => \App\Models\FranchiseInventory::where('franchise_id', $franchiseId)->sum('total_value'),
                'pending_orders' => Order::where('franchise_id', $franchiseId)->where('status', 'pending')->count(),
                'pending_payments' => PaymentSubmission::where('franchise_id', $franchiseId)->where('status', 'pending')->count(),
                'outstanding_balance' => $user->franchise?->account_balance ?? 0,
                'credit_limit' => $user->franchise?->credit_limit ?? 0,
                'credit_used_percentage' => $user->franchise?->credit_limit > 0 ? round(($user->franchise->account_balance / $user->franchise->credit_limit) * 100, 1) : 0,
                'low_stock_items' => \App\Models\FranchiseInventory::where('franchise_id', $franchiseId)->whereColumn('quantity', '<=', 'reorder_level')->count(),
            ];
            $dashboard['sales_by_category'] = \Illuminate\Support\Facades\DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')->join('products', 'products.id', '=', 'sale_items.product_id')->join('categories', 'categories.id', '=', 'products.category_id')->where('sales.franchise_id', $franchiseId)->whereMonth('sales.sale_date', $now->month)->whereYear('sales.sale_date', $now->year)->select('categories.name as category_name', \Illuminate\Support\Facades\DB::raw('SUM(sale_items.subtotal) as total_sales'), \Illuminate\Support\Facades\DB::raw('SUM(sale_items.quantity) as total_qty'))->groupBy('categories.name')->orderByDesc('total_sales')->get();
            $dashboard['sales_targets'] = \App\Models\SalesTarget::where('franchise_id', $franchiseId)->where('month', $now->month)->where('year', $now->year)->with('productCategory:id,name')->get()->map(function ($target) use ($franchiseId, $now) {
                $actualSales = Sale::where('franchise_id', $franchiseId)->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year);
                if ($target->product_category_id) { $actualSales = $actualSales->whereHas('items.product', fn($q) => $q->where('category_id', $target->product_category_id)); }
                $actual = $actualSales->sum('final_amount');
                return ['target_id' => $target->id, 'category' => $target->productCategory?->name ?? 'All Products', 'target_amount' => $target->target_amount, 'actual_amount' => $actual, 'achievement_percentage' => $target->target_amount > 0 ? round(($actual / $target->target_amount) * 100, 1) : 0];
            });
            $dashboard['inventory_status'] = \App\Models\FranchiseInventory::where('franchise_id', $franchiseId)->with('product:id,name,sku,selling_price')->get()->map(fn($item) => ['product_name' => $item->product?->name, 'sku' => $item->product?->sku, 'quantity' => $item->quantity, 'reorder_level' => $item->reorder_level, 'is_low_stock' => $item->quantity <= $item->reorder_level, 'value' => $item->quantity * ($item->product?->selling_price ?? 0)]);
        }

        return view('dashboard', compact('dashboard'));
    }

    // ── Admin Pages ──────────────────────────────────────────
    public function adminFranchises()
    {
        $franchises = Franchise::withCount(['users', 'orders', 'sales'])->latest()->paginate(20);
        return view('admin.franchises', compact('franchises'));
    }

    public function adminUsers()
    {
        $users = User::with(['role', 'franchise'])->latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function adminProducts()
    {
        $products = Product::with(['category', 'priceSlabs', 'warehouseInventory'])->latest()->paginate(20);
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        return view('admin.products', compact('products', 'categories'));
    }

    public function adminCategories()
    {
        $categories = \App\Models\Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories', compact('categories'));
    }

    public function adminOrders()
    {
        $orders = Order::with(['franchise:id,name,code', 'items.product:id,name', 'orderedByUser:id,name'])
            ->latest()
            ->paginate(20);
        $summary = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'approved' => Order::where('status', 'approved')->count(),
            'declined' => Order::where('status', 'declined')->count(),
        ];
        return view('admin.orders', compact('orders', 'summary'));
    }

    public function adminPayments()
    {
        $payments = PaymentSubmission::with('franchise:id,name,code')
            ->latest('submitted_at')
            ->paginate(20);
        $summary = [
            'total' => PaymentSubmission::count(),
            'pending' => PaymentSubmission::where('status', 'pending')->count(),
            'accepted' => PaymentSubmission::where('status', 'accepted')->count(),
            'rejected' => PaymentSubmission::where('status', 'rejected')->count(),
        ];
        return view('admin.payments', compact('payments', 'summary'));
    }

    public function adminReports(Request $request)
    {
        $type = $request->get('type', '');
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));
        $franchiseId = $request->get('franchise_id', '');
        $franchises = Franchise::where('is_active', true)->orderBy('name')->get();

        $reportData = null;
        $summary = [];
        $reportTitle = '';

        if ($type) {
            $fromDate = \Carbon\Carbon::parse($from)->startOfDay();
            $toDate = \Carbon\Carbon::parse($to)->endOfDay();

            switch ($type) {
                case 'sales':
                    $reportTitle = 'Sales Report';
                    $query = Sale::with(['franchise', 'customer', 'creator', 'items.product'])
                        ->whereBetween('sale_date', [$fromDate, $toDate]);
                    if ($franchiseId) $query->where('franchise_id', $franchiseId);
                    $reportData = $query->latest('sale_date')->get();

                    $summary = [
                        ['label' => 'Total Sales', 'value' => $reportData->sum('final_amount'), 'icon' => 'fa-money-bill-wave', 'color' => 'gradient-indigo', 'format' => 'currency'],
                        ['label' => 'Transactions', 'value' => $reportData->count(), 'icon' => 'fa-receipt', 'color' => 'gradient-green', 'format' => 'number'],
                        ['label' => 'Avg. Per Sale', 'value' => $reportData->count() ? $reportData->avg('final_amount') : 0, 'icon' => 'fa-chart-line', 'color' => 'gradient-amber', 'format' => 'currency'],
                        ['label' => 'Total Discount', 'value' => $reportData->sum('discount'), 'icon' => 'fa-percent', 'color' => 'gradient-cyan', 'format' => 'currency'],
                        ['label' => 'Paid', 'value' => $reportData->where('payment_status', 'paid')->count(), 'icon' => 'fa-check-circle', 'color' => 'gradient-green', 'format' => 'number'],
                        ['label' => 'Unpaid', 'value' => $reportData->where('payment_status', '!=', 'paid')->count(), 'icon' => 'fa-exclamation-circle', 'color' => 'gradient-rose', 'format' => 'number'],
                    ];
                    break;

                case 'orders':
                    $reportTitle = 'Order Report';
                    $query = Order::with(['franchise', 'orderedByUser', 'items.product'])
                        ->whereBetween('created_at', [$fromDate, $toDate]);
                    if ($franchiseId) $query->where('franchise_id', $franchiseId);
                    $reportData = $query->latest()->get();

                    $summary = [
                        ['label' => 'Total Orders', 'value' => $reportData->count(), 'icon' => 'fa-clipboard-list', 'color' => 'gradient-indigo', 'format' => 'number'],
                        ['label' => 'Total Value', 'value' => $reportData->sum('total_amount'), 'icon' => 'fa-money-bill-wave', 'color' => 'gradient-green', 'format' => 'currency'],
                        ['label' => 'Pending', 'value' => $reportData->where('status', 'pending')->count(), 'icon' => 'fa-clock', 'color' => 'gradient-amber', 'format' => 'number'],
                        ['label' => 'Approved', 'value' => $reportData->where('status', 'approved')->count(), 'icon' => 'fa-check-circle', 'color' => 'gradient-green', 'format' => 'number'],
                        ['label' => 'Declined', 'value' => $reportData->where('status', 'declined')->count(), 'icon' => 'fa-times-circle', 'color' => 'gradient-rose', 'format' => 'number'],
                        ['label' => 'Avg. Order Value', 'value' => $reportData->count() ? $reportData->avg('total_amount') : 0, 'icon' => 'fa-chart-bar', 'color' => 'gradient-cyan', 'format' => 'currency'],
                    ];
                    break;

                case 'payments':
                    $reportTitle = 'Payment Report';
                    $query = PaymentSubmission::with('franchise')
                        ->whereBetween('created_at', [$fromDate, $toDate]);
                    if ($franchiseId) $query->where('franchise_id', $franchiseId);
                    $reportData = $query->latest()->get();

                    $summary = [
                        ['label' => 'Total Submissions', 'value' => $reportData->count(), 'icon' => 'fa-credit-card', 'color' => 'gradient-indigo', 'format' => 'number'],
                        ['label' => 'Total Amount', 'value' => $reportData->sum('amount'), 'icon' => 'fa-money-bill-wave', 'color' => 'gradient-green', 'format' => 'currency'],
                        ['label' => 'Accepted', 'value' => $reportData->where('status', 'accepted')->count(), 'icon' => 'fa-check-circle', 'color' => 'gradient-green', 'format' => 'number'],
                        ['label' => 'Pending', 'value' => $reportData->where('status', 'pending')->count(), 'icon' => 'fa-clock', 'color' => 'gradient-amber', 'format' => 'number'],
                        ['label' => 'Rejected', 'value' => $reportData->where('status', 'rejected')->count(), 'icon' => 'fa-times-circle', 'color' => 'gradient-rose', 'format' => 'number'],
                        ['label' => 'Accepted Amount', 'value' => $reportData->where('status', 'accepted')->sum('amount'), 'icon' => 'fa-hand-holding-usd', 'color' => 'gradient-cyan', 'format' => 'currency'],
                    ];
                    break;

                case 'inventory':
                    $reportTitle = 'Inventory Report';
                    $products = Product::with(['warehouseInventory', 'franchiseInventories.franchise', 'category'])
                        ->where('is_active', true)->orderBy('name')->get();

                    $summary = [
                        ['label' => 'Total Products', 'value' => $products->count(), 'icon' => 'fa-boxes-stacked', 'color' => 'gradient-indigo', 'format' => 'number'],
                        ['label' => 'Warehouse Stock', 'value' => $products->sum(fn($p) => $p->warehouseInventory?->quantity ?? 0), 'icon' => 'fa-warehouse', 'color' => 'gradient-green', 'format' => 'number'],
                        ['label' => 'Low Stock Items', 'value' => $products->filter(fn($p) => ($p->warehouseInventory?->quantity ?? 0) <= ($p->warehouseInventory?->reorder_level ?? 0) && ($p->warehouseInventory?->quantity ?? 0) > 0)->count(), 'icon' => 'fa-exclamation-triangle', 'color' => 'gradient-amber', 'format' => 'number'],
                        ['label' => 'Out of Stock', 'value' => $products->filter(fn($p) => ($p->warehouseInventory?->quantity ?? 0) == 0)->count(), 'icon' => 'fa-times-circle', 'color' => 'gradient-rose', 'format' => 'number'],
                        ['label' => 'Total Stock Value', 'value' => $products->sum(fn($p) => ($p->warehouseInventory?->quantity ?? 0) * $p->standard_price), 'icon' => 'fa-coins', 'color' => 'gradient-cyan', 'format' => 'currency'],
                        ['label' => 'Franchise Stocks', 'value' => $products->sum(fn($p) => $p->franchiseInventories->sum('quantity')), 'icon' => 'fa-store', 'color' => 'gradient-purple', 'format' => 'number'],
                    ];
                    $reportData = $products;
                    break;

                case 'franchise':
                    $reportTitle = 'Franchise Performance';
                    $franchiseData = Franchise::withCount(['orders as total_orders' => function($q) use ($fromDate, $toDate) {
                        $q->whereBetween('created_at', [$fromDate, $toDate]);
                    }])
                    ->withCount(['sales as total_sales' => function($q) use ($fromDate, $toDate) {
                        $q->whereBetween('sale_date', [$fromDate, $toDate]);
                    }])
                    ->withSum(['sales as sales_revenue' => function($q) use ($fromDate, $toDate) {
                        $q->whereBetween('sale_date', [$fromDate, $toDate]);
                    }], 'final_amount')
                    ->withSum(['orders as orders_value' => function($q) use ($fromDate, $toDate) {
                        $q->whereBetween('created_at', [$fromDate, $toDate]);
                    }], 'total_amount')
                    ->withCount(['paymentSubmissions as payments_count' => function($q) use ($fromDate, $toDate) {
                        $q->whereBetween('created_at', [$fromDate, $toDate]);
                    }])
                    ->where('is_active', true)->orderBy('sales_revenue', 'desc')->get();

                    $reportData = $franchiseData;
                    $summary = [
                        ['label' => 'Active Franchises', 'value' => $franchiseData->count(), 'icon' => 'fa-store', 'color' => 'gradient-indigo', 'format' => 'number'],
                        ['label' => 'Total Orders', 'value' => $franchiseData->sum('total_orders'), 'icon' => 'fa-clipboard-list', 'color' => 'gradient-green', 'format' => 'number'],
                        ['label' => 'Total Sales Revenue', 'value' => $franchiseData->sum('sales_revenue'), 'icon' => 'fa-money-bill-wave', 'color' => 'gradient-amber', 'format' => 'currency'],
                        ['label' => 'Total Orders Value', 'value' => $franchiseData->sum('orders_value'), 'icon' => 'fa-shopping-cart', 'color' => 'gradient-cyan', 'format' => 'currency'],
                        ['label' => 'Total Payments', 'value' => $franchiseData->sum('payments_count'), 'icon' => 'fa-credit-card', 'color' => 'gradient-purple', 'format' => 'number'],
                        ['label' => 'Avg Sales/Franchise', 'value' => $franchiseData->count() ? $franchiseData->avg('sales_revenue') : 0, 'icon' => 'fa-chart-pie', 'color' => 'gradient-rose', 'format' => 'currency'],
                    ];
                    break;

                case 'profit-loss':
                    $reportTitle = 'Profit & Loss Report';
                    $salesQuery = Sale::whereBetween('sale_date', [$fromDate, $toDate]);
                    if ($franchiseId) $salesQuery->where('franchise_id', $franchiseId);
                    $totalSales = $salesQuery->sum('final_amount');
                    $totalDiscounts = (clone $salesQuery)->sum('discount');
                    $netRevenue = $totalSales - $totalDiscounts;

                    $costQuery = OrderItem::whereHas('order', function($q) use ($fromDate, $toDate, $franchiseId) {
                        $q->whereBetween('created_at', [$fromDate, $toDate]);
                        if ($franchiseId) $q->where('franchise_id', $franchiseId);
                    });
                    $totalCOGS = $costQuery->sum(\DB::raw('quantity * unit_price'));

                    $netProfit = $netRevenue - $totalCOGS;
                    $margin = $netRevenue > 0 ? ($netProfit / $netRevenue * 100) : 0;

                    $summary = [
                        ['label' => 'Gross Revenue', 'value' => $totalSales, 'icon' => 'fa-money-bill-wave', 'color' => 'gradient-indigo', 'format' => 'currency'],
                        ['label' => 'Discounts', 'value' => $totalDiscounts, 'icon' => 'fa-percent', 'color' => 'gradient-amber', 'format' => 'currency'],
                        ['label' => 'Net Revenue', 'value' => $netRevenue, 'icon' => 'fa-chart-line', 'color' => 'gradient-green', 'format' => 'currency'],
                        ['label' => 'Cost of Goods', 'value' => $totalCOGS, 'icon' => 'fa-truck', 'color' => 'gradient-rose', 'format' => 'currency'],
                        ['label' => 'Net Profit', 'value' => $netProfit, 'icon' => 'fa-piggy-bank', 'color' => $netProfit >= 0 ? 'gradient-green' : 'gradient-rose', 'format' => 'currency'],
                        ['label' => 'Profit Margin', 'value' => round($margin, 1), 'icon' => 'fa-percentage', 'color' => 'gradient-cyan', 'format' => 'percent'],
                    ];
                    break;
            }
        }

        return view('admin.reports', compact('type', 'from', 'to', 'franchiseId', 'franchises', 'reportData', 'summary', 'reportTitle'));
    }

    public function adminAudit()
    {
        $logs = \App\Models\ActivityLog::with('user:id,name,email')->latest()->paginate(50);
        return view('admin.audit', compact('logs'));
    }

    public function adminNews()
    {
        $news = \App\Models\News::latest()->paginate(20);
        return view('admin.news', compact('news'));
    }

    public function adminFaqs()
    {
        $faqs = \App\Models\Faq::orderBy('sort_order')->get();
        return view('admin.faqs', compact('faqs'));
    }

    public function adminSlides()
    {
        $slides = \App\Models\Slide::orderBy('sort_order')->get();
        return view('admin.slides', compact('slides'));
    }

    public function adminPages()
    {
        $pages = \App\Models\Page::latest()->paginate(20);
        return view('admin.pages', compact('pages'));
    }

    public function adminSettings()
    {
        return view('admin.settings');
    }

    public function adminSettingsSite()
    {
        return view('admin.settings-site');
    }

    public function adminSettingsUsers()
    {
        $users = User::with(['role', 'franchise'])->latest()->paginate(20);
        return view('admin.settings-users', compact('users'));
    }

    public function adminSettingsRoles()
    {
        $roles = \App\Models\Role::withCount('users')->get();
        return view('admin.settings-roles', compact('roles'));
    }

    public function adminSettingsNotifications()
    {
        return view('admin.settings-notifications');
    }

    public function adminSettingsSystem()
    {
        return view('admin.settings-system');
    }

    // ── Staff Pages ──────────────────────────────────────────
    public function staffOrders()
    {
        $orders = Order::with(['franchise', 'items.product', 'orderedByUser'])->latest()->paginate(20);
        return view('staff.orders', compact('orders'));
    }

    public function staffInventory()
    {
        $stock = WarehouseInventory::with('product:id,name,sku,unit_of_measure')->get();
        return view('staff.inventory', compact('stock'));
    }

    public function staffFranchiseStock()
    {
        $stocks = \App\Models\FranchiseInventory::with(['franchise:id,name,code', 'product:id,name,sku,unit_of_measure,selling_price'])->get();
        return view('staff.franchise-stock', compact('stocks'));
    }

    // ── Finance Pages ────────────────────────────────────────
    public function financePayments()
    {
        $payments = PaymentSubmission::with('franchise')->latest('submitted_at')->paginate(20);
        return view('finance.payments', compact('payments'));
    }

    public function financeReports()
    {
        $now = now();
        $data = [
            'total_collected_ytd' => PaymentSubmission::where('status', 'accepted')->whereYear('accepted_at', $now->year)->sum('verified_amount'),
            'total_collected_month' => PaymentSubmission::where('status', 'accepted')->whereMonth('accepted_at', $now->month)->sum('verified_amount'),
            'total_outstanding' => Franchise::where('account_balance', '>', 0)->sum('account_balance'),
            'monthly_collections' => PaymentSubmission::where('status', 'accepted')->select(\Illuminate\Support\Facades\DB::raw('MONTH(accepted_at) as month'), \Illuminate\Support\Facades\DB::raw('SUM(verified_amount) as total'))->whereYear('accepted_at', $now->year)->groupBy(\Illuminate\Support\Facades\DB::raw('MONTH(accepted_at)'))->orderBy('month')->get(),
        ];
        return view('finance.reports', compact('data'));
    }

    // ── Franchise Pages ──────────────────────────────────────
    public function franchiseOrders()
    {
        $orders = Order::forFranchise(auth()->user()->franchise_id)->with('items.product')->latest()->paginate(20);
        return view('franchise.orders', compact('orders'));
    }

    public function franchiseSales()
    {
        $sales = Sale::where('franchise_id', auth()->user()->franchise_id)->with(['customer', 'items.product'])->latest()->paginate(20);
        return view('franchise.sales', compact('sales'));
    }

    public function franchiseInventory()
    {
        $inventory = \App\Models\FranchiseInventory::where('franchise_id', auth()->user()->franchise_id)->with('product:id,name,sku,unit_of_measure,selling_price')->get();
        return view('franchise.inventory', compact('inventory'));
    }

    public function franchisePayments()
    {
        $payments = PaymentSubmission::where('franchise_id', auth()->user()->franchise_id)->latest('submitted_at')->paginate(20);
        return view('franchise.payments', compact('payments'));
    }

    public function franchiseChat()
    {
        return view('franchise.chat');
    }

    // ── Profile ─────────────────────────────────────────────
    public function profile()
    {
        $user = auth()->user()->load(['role', 'franchise', 'branch']);
        return view('profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
        ]);
        $user->update($validated);
        return redirect()->route('web.profile')->with('success', 'Profile updated successfully!');
    }

    public function profilePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = auth()->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        $user->update(['password' => $request->password]);
        return redirect()->route('web.profile')->with('success', 'Password changed successfully!');
    }

    public function profileAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $user = auth()->user();
        if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);
        return redirect()->route('web.profile')->with('success', 'Profile picture updated!');
    }
}
