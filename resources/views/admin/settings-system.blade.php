@extends('layouts.app')
@section('title', 'System Information')
@section('page-title', 'System Information')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1">
        <div class="card-full"><div class="card-body p-2">
            <nav class="space-y-1">
                <a href="{{ route('web.admin.settings.general') }}" class="sidebar-link" style="margin:0"><i class="fas fa-cog w-5 text-center text-sm"></i> General</a>
                <a href="{{ route('web.admin.settings.users') }}" class="sidebar-link" style="margin:0"><i class="fas fa-users w-5 text-center text-sm"></i> User Management</a>
                <a href="{{ route('web.admin.settings.roles') }}" class="sidebar-link" style="margin:0"><i class="fas fa-shield-halved w-5 text-center text-sm"></i> Roles & Permissions</a>
                <a href="{{ route('web.admin.settings.system') }}" class="sidebar-link active" style="margin:0"><i class="fas fa-server w-5 text-center text-sm"></i> System Info</a>
            </nav>
        </div></div>
    </div>
    <div class="lg:col-span-3 space-y-4">
        <div class="card-full">
            <div class="card-header"><h3 class="text-sm font-semibold" style="color:var(--text-primary)">System Overview</h3></div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">App Name</span><span class="font-medium" style="color:var(--text-primary)">Farmmantra Agro Chemicals</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Laravel Version</span><span class="font-medium" style="color:var(--text-primary)">{{ app()->version() }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">PHP Version</span><span class="font-medium" style="color:var(--text-primary)">{{ phpversion() }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Environment</span><span class="badge badge-success">{{ config('app.env') }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Debug Mode</span><span class="badge {{ config('app.debug') ? 'badge-warning' : 'badge-success' }}">{{ config('app.debug') ? 'ON' : 'OFF' }}</span></div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Timezone</span><span class="font-medium" style="color:var(--text-primary)">{{ config('app.timezone') }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Session Driver</span><span class="font-medium" style="color:var(--text-primary)">{{ config('session.driver') }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Cache Driver</span><span class="font-medium" style="color:var(--text-primary)">{{ config('cache.store') }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Queue Driver</span><span class="font-medium" style="color:var(--text-primary)">{{ config('queue.default') }}</span></div>
                        <div class="flex justify-between text-sm"><span style="color:var(--text-muted)">Database</span><span class="font-medium" style="color:var(--text-primary)">{{ config('database.connections.mysql.database') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-full">
            <div class="card-header"><h3 class="text-sm font-semibold" style="color:var(--text-primary)">Database Statistics</h3></div>
            <div class="card-body">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @php
                    $stats = [
                        ['label' => 'Users', 'count' => \App\Models\User::count(), 'icon' => 'fa-users', 'color' => 'gradient-indigo'],
                        ['label' => 'Franchises', 'count' => \App\Models\Franchise::count(), 'icon' => 'fa-store', 'color' => 'gradient-green'],
                        ['label' => 'Products', 'count' => \App\Models\Product::count(), 'icon' => 'fa-boxes-stacked', 'color' => 'gradient-amber'],
                        ['label' => 'Categories', 'count' => \App\Models\Category::count(), 'icon' => 'fa-tags', 'color' => 'gradient-cyan'],
                        ['label' => 'Orders', 'count' => \App\Models\Order::count(), 'icon' => 'fa-clipboard-list', 'color' => 'gradient-purple'],
                        ['label' => 'Sales', 'count' => \App\Models\Sale::count(), 'icon' => 'fa-shopping-cart', 'color' => 'gradient-green'],
                        ['label' => 'Payments', 'count' => \App\Models\PaymentSubmission::count(), 'icon' => 'fa-money-bill', 'color' => 'gradient-rose'],
                        ['label' => 'Activity Logs', 'count' => \App\Models\ActivityLog::count(), 'icon' => 'fa-shield-halved', 'color' => 'gradient-indigo'],
                    ];
                    @endphp
                    @foreach($stats as $s)
                    <div class="card-stat text-center">
                        <div class="h-10 w-10 rounded-lg {{ $s['color'] }} flex items-center justify-center mx-auto mb-2">
                            <i class="fas {{ $s['icon'] }} text-white text-sm"></i>
                        </div>
                        <p class="text-xl font-bold" style="color:var(--text-primary)">{{ $s['count'] }}</p>
                        <p class="text-xs" style="color:var(--text-muted)">{{ $s['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
