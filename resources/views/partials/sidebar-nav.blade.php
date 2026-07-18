@php
    $user = auth()->user();
    $role = $user->role?->name ?? '';
    $current = request()->route()->getName();
    $showBadges = ($notif['notif_inapp_badge_counts'] ?? '1') === '1';
    $pending_orders = $showBadges ? \App\Models\Order::where('status', 'pending')->count() : 0;
    if ($role === 'Franchise Partner') {
        $pending_payments = $showBadges ? \App\Models\PaymentSubmission::where('status', 'pending')->where('franchise_id', $user->franchise_id)->count() : 0;
    } else {
        $pending_payments = $showBadges ? \App\Models\PaymentSubmission::where('status', 'pending')->count() : 0;
    }
    $pending_count = $role === 'System Administrator' ? $pending_orders + $pending_payments : ($role === 'Farmmantra Staff' ? $pending_orders : ($role === 'Finance Department' ? $pending_payments : 0));
@endphp

<div class="flex h-16 items-center gap-3 px-5 border-b" style="border-color:rgba(255,255,255,0.08)">
    <div class="h-9 w-9 rounded-lg overflow-hidden flex-shrink-0">
        @if(!empty($site['site_logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($site['site_logo']))
        <img src="{{ asset('storage/'.$site['site_logo']) }}" alt="{{ $site['site_name'] ?? 'FM' }}" class="h-full w-full object-contain" style="background:rgba(255,255,255,0.05)">
        @else
        <div class="h-full w-full gradient-indigo flex items-center justify-center">
            <span class="text-white font-bold text-sm">FM</span>
        </div>
        @endif
    </div>
    <div>
        <span class="text-white font-bold text-base leading-tight block">{{ $site['site_name'] ?? 'Farmmantra' }}</span>
        <span class="text-gray-400 text-[10px] font-medium tracking-wider uppercase">{{ $site['site_tagline'] ?? 'Agro Chemicals' }}</span>
    </div>
</div>

<nav class="flex flex-1 flex-col py-3 px-3 overflow-y-auto" style="scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent">
    <ul class="space-y-0.5">

        <li>
            <a href="{{ route('web.dashboard') }}" class="sidebar-link {{ str_starts_with($current, 'web.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large w-5 text-center text-sm"></i> Dashboard
            </a>
        </li>

        {{-- ═══ ADMIN ═══ --}}
        @if($role === 'System Administrator')

        {{-- Management --}}
        <li><div class="sidebar-section">Management</div></li>
        <li>
            <a href="{{ route('web.admin.franchises') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.franchise') ? 'active' : '' }}">
                <i class="fas fa-store w-5 text-center text-sm"></i> Franchises
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.users') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.user') ? 'active' : '' }}">
                <i class="fas fa-users w-5 text-center text-sm"></i> Users
            </a>
        </li>

        {{-- Products & Inventory --}}
        <li><div class="sidebar-section">Products & Inventory</div></li>
        <li>
            <a href="{{ route('web.admin.products') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.product') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked w-5 text-center text-sm"></i> Products
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.categories') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.categor') ? 'active' : '' }}">
                <i class="fas fa-tags w-5 text-center text-sm"></i> Categories
            </a>
        </li>

        {{-- Orders & Payments --}}
        <li><div class="sidebar-section">Orders & Payments</div></li>
        <li>
            <a href="{{ route('web.admin.orders') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.order') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5 text-center text-sm"></i> All Orders
                @if($pending_orders > 0)
                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pending_orders }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.payments') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.payment') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5 text-center text-sm"></i> Payments
                @if($pending_payments > 0)
                <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pending_payments }}</span>
                @endif
            </a>
        </li>

        {{-- Finance --}}
        <li><div class="sidebar-section">Finance & Reports</div></li>
        <li>
            <a href="{{ route('web.admin.reports') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.report') ? 'active' : '' }}">
                <i class="fas fa-chart-bar w-5 text-center text-sm"></i> Reports
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.audit') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.audit') ? 'active' : '' }}">
                <i class="fas fa-shield-halved w-5 text-center text-sm"></i> Audit Logs
            </a>
        </li>

        {{-- Content --}}
        <li><div class="sidebar-section">Content</div></li>
        <li>
            <a href="{{ route('web.admin.news') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.news') ? 'active' : '' }}">
                <i class="fas fa-newspaper w-5 text-center text-sm"></i> News & Events
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.faqs') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.faq') ? 'active' : '' }}">
                <i class="fas fa-circle-question w-5 text-center text-sm"></i> FAQs
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.slides') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.slide') ? 'active' : '' }}">
                <i class="fas fa-images w-5 text-center text-sm"></i> Slides / Banners
            </a>
        </li>
        <li>
            <a href="{{ route('web.admin.pages') }}" class="sidebar-link {{ str_starts_with($current, 'web.admin.page') ? 'active' : '' }}">
                <i class="fas fa-file-lines w-5 text-center text-sm"></i> Pages
            </a>
        </li>

        <li><div class="sidebar-section">System</div></li>
        <li>
            <a href="{{ route('web.admin.settings.general') }}" class="sidebar-link {{ request()->routeIs('web.admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog w-5 text-center text-sm"></i> Settings
            </a>
        </li>
        @endif

        {{-- ═══ STAFF ═══ --}}
        @if($role === 'Farmmantra Staff')
        <li><div class="sidebar-section">Operations</div></li>
        <li>
            <a href="{{ route('web.staff.orders') }}" class="sidebar-link {{ str_starts_with($current, 'web.staff.order') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5 text-center text-sm"></i> Orders
                @if($pending_orders > 0)
                <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pending_orders }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('web.staff.inventory') }}" class="sidebar-link {{ str_starts_with($current, 'web.staff.inventory') ? 'active' : '' }}">
                <i class="fas fa-warehouse w-5 text-center text-sm"></i> Warehouse Stock
            </a>
        </li>
        <li>
            <a href="{{ route('web.staff.franchiseStock') }}" class="sidebar-link {{ str_starts_with($current, 'web.staff.franchiseStock') ? 'active' : '' }}">
                <i class="fas fa-store w-5 text-center text-sm"></i> Franchise Stock
            </a>
        </li>
        @endif

        {{-- ═══ FINANCE ═══ --}}
        @if($role === 'Finance Department')
        <li><div class="sidebar-section">Finance</div></li>
        <li>
            <a href="{{ route('web.finance.payments') }}" class="sidebar-link {{ str_starts_with($current, 'web.finance.payment') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5 text-center text-sm"></i> Payments
                @if($pending_payments > 0)
                <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pending_payments }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('web.finance.reports') }}" class="sidebar-link {{ str_starts_with($current, 'web.finance.report') ? 'active' : '' }}">
                <i class="fas fa-chart-pie w-5 text-center text-sm"></i> Reports
            </a>
        </li>
        @endif

        {{-- ═══ FRANCHISE ═══ --}}
        @if($role === 'Franchise Partner')
        <li><div class="sidebar-section">My Franchise</div></li>
        <li>
            <a href="{{ route('web.franchise.orders') }}" class="sidebar-link {{ str_starts_with($current, 'web.franchise.order') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5 text-center text-sm"></i> Orders
            </a>
        </li>
        <li>
            <a href="{{ route('web.franchise.sales') }}" class="sidebar-link {{ str_starts_with($current, 'web.franchise.sale') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart w-5 text-center text-sm"></i> Sales
            </a>
        </li>
        <li>
            <a href="{{ route('web.franchise.inventory') }}" class="sidebar-link {{ str_starts_with($current, 'web.franchise.inventory') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked w-5 text-center text-sm"></i> Inventory
            </a>
        </li>
        <li>
            <a href="{{ route('web.franchise.payments') }}" class="sidebar-link {{ str_starts_with($current, 'web.franchise.payment') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5 text-center text-sm"></i> Payments
                @if($pending_payments > 0)
                <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pending_payments }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('web.franchise.chat') }}" class="sidebar-link {{ str_starts_with($current, 'web.franchise.chat') ? 'active' : '' }}">
                <i class="fas fa-comments w-5 text-center text-sm"></i> Messages
            </a>
        </li>
        @endif

        {{-- ═══ ALL ROLES — Profile ═══ --}}
        <li><div class="sidebar-section">Account</div></li>
        <li>
            <a href="{{ route('web.profile') }}" class="sidebar-link {{ request()->routeIs('web.profile*') ? 'active' : '' }}">
                <i class="fas fa-user-circle w-5 text-center text-sm"></i> My Profile
            </a>
        </li>

    </ul>

    {{-- Bottom User Info --}}
    <div class="mt-auto pt-4 border-t" style="border-color:rgba(255,255,255,0.08)">
        <a href="{{ route('web.profile') }}" class="flex items-center gap-3 px-2 py-3 rounded-lg transition" style="background:rgba(255,255,255,0.04); text-decoration:none" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
            @if($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
            <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="h-8 w-8 rounded-full object-cover flex-shrink-0">
            @else
            <div class="h-8 w-8 rounded-full gradient-indigo flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-medium truncate">{{ $user->name }}</p>
                <p class="text-gray-400 text-xs truncate">{{ $user->email }}</p>
            </div>
            <i class="fas fa-chevron-right text-[10px]" style="color:rgba(255,255,255,0.3)"></i>
        </a>
    </div>
</nav>
