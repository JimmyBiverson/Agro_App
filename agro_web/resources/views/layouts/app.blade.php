<!DOCTYPE html>
<html lang="en" class="h-full" x-data="theme()" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $site['site_name'] ?? 'Farmmantra' }}</title>
    {{-- Dynamic Favicon --}}
    @if(!empty($site['site_favicon']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($site['site_favicon']))
    <link rel="icon" type="image/png" href="{{ asset('storage/'.$site['site_favicon']) }}">
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    {{-- Dynamic OG Meta Tags --}}
    <meta property="og:title" content="@yield('title', 'Dashboard') - {{ $site['site_name'] ?? 'Farmmantra' }}">
    <meta property="og:site_name" content="{{ $site['site_name'] ?? 'Farmmantra' }}">
    <meta property="og:description" content="{{ $site['site_tagline'] ?? 'Agro Chemicals Limited' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($site['site_logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($site['site_logo']))
    <meta property="og:image" content="{{ asset('storage/'.$site['site_logo']) }}">
    @elseif(!empty($site['og_image']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($site['og_image']))
    <meta property="og:image" content="{{ asset('storage/'.$site['og_image']) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Dashboard') - {{ $site['site_name'] ?? 'Farmmantra' }}">
    <meta name="twitter:description" content="{{ $site['site_tagline'] ?? 'Agro Chemicals Limited' }}">
    @if(!empty($site['og_image']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($site['og_image']))
    <meta name="twitter:image" content="{{ asset('storage/'.$site['og_image']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        :root {
            --bg-body: #f0f2f5;
            --bg-card: rgba(255,255,255,0.85);
            --bg-card-solid: #ffffff;
            --bg-sidebar: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            --bg-sidebar-hover: rgba(255,255,255,0.06);
            --bg-sidebar-active: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --bg-topbar: rgba(255,255,255,0.8);
            --bg-input: rgba(0,0,0,0.03);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --text-sidebar: #94a3b8;
            --text-sidebar-active: #ffffff;
            --border-color: rgba(0,0,0,0.06);
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 1px 3px rgba(0,0,0,0.04), 0 2px 8px rgba(0,0,0,0.03);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.08);
            --accent: #6366f1;
            --accent-light: rgba(99,102,241,0.08);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --glass-blur: blur(20px);
        }
        html.dark {
            --bg-body: #0c0f1a;
            --bg-card: rgba(30,41,59,0.7);
            --bg-card-solid: #1e293b;
            --bg-sidebar: linear-gradient(180deg, #020617 0%, #0f172a 100%);
            --bg-sidebar-hover: rgba(255,255,255,0.04);
            --bg-sidebar-active: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --bg-topbar: rgba(15,23,42,0.8);
            --bg-input: rgba(255,255,255,0.04);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --text-sidebar: #64748b;
            --border-color: rgba(255,255,255,0.06);
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.2);
            --shadow: 0 1px 3px rgba(0,0,0,0.2), 0 2px 8px rgba(0,0,0,0.15);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.3);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.4);
            --accent-light: rgba(99,102,241,0.15);
        }
        * { font-family: 'Inter', sans-serif; }
        html, body { height: 100%; overflow: hidden; background: var(--bg-body); color: var(--text-primary); transition: background 0.3s, color 0.3s; }

        /* Dynamic theme overrides from admin settings */
        @if(!empty($site['theme_accent']))
        :root { --accent: {{ $site['theme_accent'] }}; --accent-light: {{ $site['theme_accent'] }}14; }
        html.dark { --accent-light: {{ $site['theme_accent'] }}26; }
        .gradient-indigo { background: linear-gradient(135deg, {{ $site['theme_accent'] }} 0%, {{ $site['theme_accent'] }}cc 100%) !important; }
        @endif
        @if(!empty($site['theme_success'])) :root { --success: {{ $site['theme_success'] }}; } @endif
        @if(!empty($site['theme_warning'])) :root { --warning: {{ $site['theme_warning'] }}; } @endif
        @if(!empty($site['theme_danger'])) :root { --danger: {{ $site['theme_danger'] }}; } @endif
        @if(!empty($site['theme_info'])) :root { --info: {{ $site['theme_info'] }}; } @endif

        .sidebar {
            background: var(--bg-sidebar); width: 260px; height: 100vh;
            position: fixed; top: 0; left: 0; z-index: 40;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar::-webkit-scrollbar { width: 3px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 10px; }

        .main-content { margin-left: 260px; height: 100vh; overflow-y: auto; transition: margin 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        .main-content::-webkit-scrollbar { width: 6px; }
        .main-content::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 10px; }
        html.dark .main-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }

        .topbar {
            background: var(--bg-topbar); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
            border-bottom: 1px solid var(--border-color); height: 64px;
            position: sticky; top: 0; z-index: 30;
        }

        .card-stat {
            background: var(--bg-card); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border-color); border-radius: 16px; padding: 1.25rem;
            box-shadow: var(--shadow); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-stat:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .card-full {
            background: var(--bg-card); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border-color); border-radius: 16px; box-shadow: var(--shadow);
        }
        .card-full .card-header {
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-full .card-body { padding: 1.25rem; }

        .sidebar-link {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 1.25rem;
            color: var(--text-sidebar); text-decoration: none; font-size: 0.85rem; font-weight: 500;
            border-radius: 10px; margin: 2px 10px; transition: all 0.2s; position: relative;
        }
        .sidebar-link:hover { background: var(--bg-sidebar-hover); color: #e2e8f0; }
        .sidebar-link.active {
            background: var(--bg-sidebar-active); color: var(--text-sidebar-active);
            box-shadow: 0 4px 15px rgba(99,102,241,0.35); font-weight: 600;
        }

        .sidebar-section {
            padding: 0.75rem 1.25rem 0.25rem; font-size: 0.65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.2);
        }

        .badge { display: inline-flex; align-items: center; padding: 0.2rem 0.65rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: rgba(16,185,129,0.12); color: #059669; }
        .badge-warning { background: rgba(245,158,11,0.12); color: #d97706; }
        .badge-danger { background: rgba(239,68,68,0.12); color: #dc2626; }
        .badge-info { background: rgba(6,182,212,0.12); color: #0891b2; }
        .badge-primary { background: rgba(99,102,241,0.12); color: #6366f1; }
        html.dark .badge-success { background: rgba(16,185,129,0.15); color: #34d399; }
        html.dark .badge-warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
        html.dark .badge-danger { background: rgba(239,68,68,0.15); color: #f87171; }
        html.dark .badge-info { background: rgba(6,182,212,0.15); color: #22d3ee; }
        html.dark .badge-primary { background: rgba(99,102,241,0.15); color: #818cf8; }

        .table-dark th {
            background: var(--bg-input); color: var(--text-muted); font-size: 0.7rem;
            font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
            border-bottom: 1px solid var(--border-color) !important;
        }
        .table-dark td { border-color: var(--border-color) !important; color: var(--text-primary); }
        .table-dark { --bs-table-bg: transparent; --bs-table-hover-bg: var(--bg-input); }
        .table-dark tbody tr { transition: background 0.15s; }
        .table-dark tbody tr:hover { background: var(--bg-input); }

        .gradient-indigo { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        .gradient-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .gradient-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .gradient-rose { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .gradient-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .gradient-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

        .page-enter { animation: pageSlide 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes pageSlide { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .fade-number { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); width: 280px; max-width: 85vw; }
            .sidebar.open { transform: translateX(0); box-shadow: 0 0 60px rgba(0,0,0,0.5); }
            .main-content { margin-left: 0; }
        }
        @media (max-width: 480px) {
            .card-full .card-header { flex-wrap: wrap; gap: 0.5rem; }
            .card-stat { padding: 1rem; }
        }
    </style>
    @yield('head')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden transition-opacity" @click="sidebarOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-150"></div>

    {{-- Sidebar --}}
    <aside class="sidebar" :class="{ 'open': sidebarOpen }">
        @include('partials.sidebar-nav')
    </aside>

    {{-- Main --}}
    <div class="main-content">
        {{-- Topbar --}}
        <header class="topbar flex items-center px-3 sm:px-4 lg:px-6 justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-xl transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                    <i class="fas fa-bars" style="color:var(--text-muted)"></i>
                </button>
                <div class="hidden sm:block">
                    <h1 class="text-lg font-bold" style="color:var(--text-primary)">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs" style="color:var(--text-muted)">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="toggle()" class="p-2.5 rounded-xl transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''" :title="dark ? 'Light Mode' : 'Dark Mode'">
                    <i x-show="!dark" class="fas fa-moon" style="color:var(--text-muted)"></i>
                    <i x-show="dark" class="fas fa-sun text-yellow-400"></i>
                </button>
                {{-- Notification Bell --}}
                @php
                    $role = auth()->user()->role?->name;
                    $showBadges = ($notif['notif_inapp_badge_counts'] ?? '1') === '1';
                    $nOrders = $showBadges ? \App\Models\Order::where('status', 'pending')->count() : 0;
                    $nPayments = $showBadges ? \App\Models\PaymentSubmission::where('status', 'pending')->count() : 0;
                    $nLowStock = $showBadges ? \App\Models\WarehouseInventory::whereColumn('quantity', '<=', 'reorder_level')->count() : 0;
                    $nPendingTotal = \App\Models\PaymentSubmission::where('status', 'pending')->sum('amount');
                    $fid = auth()->user()->franchise_id;
                    $nMyOrders = $fid ? \App\Models\Order::where('franchise_id', $fid)->where('status', 'pending')->count() : 0;
                    $nMyPayments = $fid ? \App\Models\PaymentSubmission::where('franchise_id', $fid)->where('status', 'pending')->count() : 0;
                    $nMyLowStock = $fid ? \App\Models\FranchiseInventory::where('franchise_id', $fid)->whereColumn('quantity', '<=', 'reorder_level')->count() : 0;
                    $bellCount = 0;
                    if ($role === 'System Administrator') { $bellCount = ($nOrders > 0 ? 1 : 0) + ($nPayments > 0 ? 1 : 0) + ($nLowStock > 0 ? 1 : 0); }
                    elseif ($role === 'Farmmantra Staff') { $bellCount = ($nOrders > 0 ? 1 : 0) + ($nLowStock > 0 ? 1 : 0); }
                    elseif ($role === 'Finance Department') { $bellCount = $nPayments > 0 ? 1 : 0; }
                    elseif ($role === 'Franchise Partner') { $bellCount = ($nMyOrders > 0 ? 1 : 0) + ($nMyPayments > 0 ? 1 : 0) + ($nMyLowStock > 0 ? 1 : 0); }
                @endphp
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" class="p-2.5 rounded-xl relative transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                        <i class="fas fa-bell" style="color:var(--text-muted)"></i>
                        @if($bellCount > 0)
                        <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full animate-pulse"></span>
                        @endif
                    </button>
                    <div x-show="notifOpen" @click.outside="notifOpen = false" x-cloak x-transition
                         class="absolute right-0 top-full mt-2 w-80 rounded-2xl border py-2 z-50"
                         style="background:var(--bg-card-solid); border-color:var(--border-color); box-shadow:var(--shadow-lg); backdrop-filter:var(--glass-blur)">
                        <div class="px-4 py-2.5 border-b flex items-center justify-between" style="border-color:var(--border-color)">
                            <span class="text-xs font-bold uppercase tracking-wider" style="color:var(--text-muted)">Notifications</span>
                            @if($bellCount > 0)
                            <span class="badge badge-danger text-[10px]">{{ $bellCount }}</span>
                            @endif
                        </div>

                        {{-- Admin Notifications --}}
                        @if($role === 'System Administrator')
                            @if($nOrders > 0)
                            <a href="{{ route('web.admin.orders') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-amber flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-clipboard-list text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nOrders }} pending order{{ $nOrders > 1 ? 's' : '' }} to review</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                            @if($nPayments > 0)
                            <a href="{{ route('web.admin.payments') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-purple flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-money-bill-wave text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nPayments }} pending payment{{ $nPayments > 1 ? 's' : '' }} to verify</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                            @if($nLowStock > 0)
                            <a href="{{ route('web.admin.products') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-rose flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-exclamation-triangle text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nLowStock }} product{{ $nLowStock > 1 ? 's' : '' }} low in warehouse</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                        @endif

                        {{-- Staff Notifications --}}
                        @if($role === 'Farmmantra Staff')
                            @if($nOrders > 0)
                            <a href="{{ route('web.staff.orders') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-amber flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-clipboard-list text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nOrders }} pending order{{ $nOrders > 1 ? 's' : '' }} to approve</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                            @if($nLowStock > 0)
                            <a href="{{ route('web.staff.inventory') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-rose flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-exclamation-triangle text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nLowStock }} product{{ $nLowStock > 1 ? 's' : '' }} low in warehouse</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                        @endif

                        {{-- Finance Notifications --}}
                        @if($role === 'Finance Department')
                            @if($nPayments > 0)
                            <a href="{{ route('web.finance.payments') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-amber flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-money-bill-wave text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nPayments }} payment{{ $nPayments > 1 ? 's' : '' }} to verify</p>
                                    <p class="text-[10px] mt-0.5" style="color:var(--text-muted)">Total: UGX {{ number_format($nPendingTotal) }}</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                        @endif

                        {{-- Franchise Notifications --}}
                        @if($role === 'Franchise Partner')
                            @if($nMyOrders > 0)
                            <a href="{{ route('web.franchise.orders') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-amber flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-clock text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">Your order{{ $nMyOrders > 1 ? 's' : '' }} awaiting approval</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                            @if($nMyPayments > 0)
                            <a href="{{ route('web.franchise.payments') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-purple flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-money-bill-wave text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">Your payment{{ $nMyPayments > 1 ? 's' : '' }} under review</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                            @if($nMyLowStock > 0)
                            <a href="{{ route('web.franchise.inventory') }}" @click="notifOpen = false" class="flex items-start gap-3 px-4 py-3 transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                                <div class="h-8 w-8 rounded-lg gradient-rose flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-exclamation-triangle text-white text-xs"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $nMyLowStock }} item{{ $nMyLowStock > 1 ? 's' : '' }} low in your stock</p>
                                    <p class="text-[10px] mt-0.5 font-semibold" style="color:var(--accent)">View Details →</p>
                                </div>
                            </a>
                            @endif
                        @endif

                        {{-- Empty State --}}
                        @if($bellCount === 0)
                        <div class="px-4 py-6 text-center">
                            <i class="fas fa-check-circle text-xl mb-2" style="color:var(--success)"></i>
                            <p class="text-xs font-medium" style="color:var(--text-muted)">All caught up!</p>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="w-px h-8 mx-1" style="background:var(--border-color)"></div>
                <div class="flex items-center gap-3" x-data="{ open: false }">
                    <a href="{{ route('web.profile') }}" class="h-9 w-9 rounded-xl gradient-indigo flex items-center justify-center text-white text-sm font-bold shadow-md shadow-indigo-500/20 no-underline" style="text-decoration:none">
                        @if(auth()->user()->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->avatar))
                        <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="" class="h-full w-full object-cover rounded-xl">
                        @else
                        {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </a>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold leading-tight" style="color:var(--text-primary)">{{ auth()->user()->name }}</p>
                        <p class="text-xs" style="color:var(--text-muted)">{{ auth()->user()->role?->name }}</p>
                    </div>
                    <button @click="open = !open" class="p-1.5 rounded-lg transition" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                        <i class="fas fa-chevron-down text-[10px]" style="color:var(--text-muted)"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak x-transition
                         class="absolute right-0 top-full mt-2 w-52 rounded-2xl border py-2 z-50"
                         style="background:var(--bg-card-solid); border-color:var(--border-color); box-shadow:var(--shadow-lg); backdrop-filter:var(--glass-blur)">
                        <a href="{{ route('web.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition" style="color:var(--text-primary)" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                            <i class="fas fa-user text-xs w-4 text-center" style="color:var(--text-muted)"></i> <span class="hidden sm:inline">My Profile</span>
                        </a>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('web.admin.settings.general') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition" style="color:var(--text-primary)" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background=''">
                            <i class="fas fa-cog text-xs w-4 text-center" style="color:var(--text-muted)"></i> <span class="hidden sm:inline">Settings</span>
                        </a>
                        @endif
                        <hr class="my-1.5" style="border-color:var(--border-color)">
                        <form method="POST" action="{{ route('web.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 transition" onmouseover="this.style.background='rgba(239,68,68,0.06)'" onmouseout="this.style.background=''">
                                <i class="fas fa-sign-out-alt text-xs w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="p-3 sm:p-4 lg:p-6 page-enter">
            @if(session('success'))
                <div class="mb-5 rounded-2xl p-4 flex items-center gap-3" style="background:var(--bg-card); border:1px solid rgba(16,185,129,0.2); box-shadow:var(--shadow)">
                    <div class="h-9 w-9 rounded-xl gradient-green flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <p class="text-sm font-medium" style="color:var(--success)">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 rounded-2xl p-4 flex items-center gap-3" style="background:var(--bg-card); border:1px solid rgba(239,68,68,0.2); box-shadow:var(--shadow)">
                    <div class="h-9 w-9 rounded-xl gradient-rose flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation text-white text-sm"></i>
                    </div>
                    <p class="text-sm font-medium" style="color:var(--danger)">{{ session('error') }}</p>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script>
        function theme() {
            return {
                dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggle() {
                    this.dark = !this.dark;
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                }
            }
        }

        // Auto-refresh dashboard if enabled
        @if(($notif['notif_inapp_auto_refresh'] ?? '1') === '1')
        setInterval(function() {
            if (!document.hidden && window.location.pathname.indexOf('/dashboard') !== -1) {
                window.location.reload();
            }
        }, 60000);
        @endif

        // Toast notification system
        function showToast(message, type) {
            type = type || 'info';
            var colors = { success: 'var(--success)', warning: 'var(--warning)', danger: 'var(--danger)', info: 'var(--accent)' };
            var icons = { success: 'fa-check-circle', warning: 'fa-exclamation-triangle', danger: 'fa-circle-xmark', info: 'fa-circle-info' };
            var toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:12px;padding:14px 20px;border-radius:16px;color:white;font-size:14px;font-weight:500;box-shadow:0 8px 30px rgba(0,0,0,0.3);transform:translateY(20px);opacity:0;transition:all 0.3s cubic-bezier(0.16,1,0.3,1);backdrop-filter:blur(20px);background:rgba(30,41,59,0.95);border:1px solid rgba(255,255,255,0.1);';
            toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '" style="color:' + (colors[type] || colors.info) + '"></i><span>' + message + '</span>';
            document.body.appendChild(toast);
            requestAnimationFrame(function() { toast.style.transform = 'translateY(0)'; toast.style.opacity = '1'; });
            setTimeout(function() { toast.style.transform = 'translateY(20px)'; toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 300); }, 4000);
        }

        // Show session toast notifications if enabled
        @if(($notif['notif_inapp_toasts'] ?? '1') === '1')
            @if(session('success')) showToast('{{ session("success") }}', 'success'); @endif
            @if(session('error')) showToast('{{ session("error") }}', 'danger'); @endif
        @endif
    </script>
</body>
</html>
