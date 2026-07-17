@extends('layouts.app')
@section('title', 'Notification Settings')
@section('page-title', 'Notification Settings')

@php
    $settingsNav = [
        ['route' => 'web.admin.settings.site', 'label' => 'Site Identity', 'icon' => 'fa-palette'],
        ['route' => 'web.admin.settings.general', 'label' => 'General', 'icon' => 'fa-cog'],
        ['route' => 'web.admin.settings.users', 'label' => 'User Management', 'icon' => 'fa-user-gear'],
        ['route' => 'web.admin.settings.roles', 'label' => 'Roles & Permissions', 'icon' => 'fa-shield-halved'],
        ['route' => 'web.admin.settings.notifications', 'label' => 'Notifications', 'icon' => 'fa-bell'],
        ['route' => 'web.admin.settings.system', 'label' => 'System Info', 'icon' => 'fa-server'],
    ];
@endphp

@section('content')
<div class="card-full mb-6">
    <div class="card-body py-4 px-5">
        <div class="flex gap-2 overflow-x-auto" style="scrollbar-width:none">
            @foreach($settingsNav as $nav)
            <a href="{{ route($nav['route']) }}"
               class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs($nav['route']) ? 'text-white shadow-lg' : '' }}"
               style="{{ request()->routeIs($nav['route'])
                   ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 4px 15px rgba(99,102,241,0.4)'
                   : 'color:var(--text-primary); background:var(--bg-card); border:1px solid var(--border-color)' }}">
                <i class="fas {{ $nav['icon'] }} {{ request()->routeIs($nav['route']) ? '' : 'opacity-60' }}"></i>
                {{ $nav['label'] }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card-full">
    <div class="card-header">
        <div>
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Notification Settings</h3>
            <p class="text-xs" style="color:var(--text-muted)">Configure how and when notifications are sent</p>
        </div>
    </div>
    <div class="card-body">
        <form class="space-y-8">
            {{-- Email Notifications --}}
            <div>
                <h4 class="text-xs font-semibold mb-4" style="color:var(--text-secondary)">Email Notifications</h4>
                <div class="space-y-3">
                    @foreach([
                        ['New order placed', 'Notify admin when a franchise partner places a new order'],
                        ['Order approved / declined', 'Notify franchise when their order status changes'],
                        ['Payment submitted', 'Notify finance team when proof of payment is uploaded'],
                        ['Payment verified', 'Notify franchise when payment is accepted or rejected'],
                        ['Low stock alert', 'Alert staff when warehouse inventory drops below reorder level'],
                        ['New user registered', 'Notify admin when a new user account is created'],
                        ['Franchise deactivated', 'Notify admin when a franchise account is deactivated'],
                    ] as [$label, $desc])
                    <div class="flex items-center justify-between rounded-xl p-4 border" style="border-color:var(--border-color); background:var(--bg-card)">
                        <div>
                            <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $label }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $desc }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-9 h-5 rounded-full peer peer-checked:bg-indigo-600 transition" style="background:var(--border-color)"></div>
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full peer-checked:translate-x-4 transition"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- In-App Notifications --}}
            <div class="pt-4 border-t" style="border-color:var(--border-color)">
                <h4 class="text-xs font-semibold mb-4" style="color:var(--text-secondary)">In-App Notifications</h4>
                <div class="space-y-3">
                    @foreach([
                        ['Show badge counts on sidebar', 'Display pending order and payment counts in the navigation'],
                        ['Show toast notifications', 'Display brief notification popups in the dashboard'],
                        ['Auto-refresh dashboard', 'Periodically refresh dashboard data for real-time updates'],
                    ] as [$label, $desc])
                    <div class="flex items-center justify-between rounded-xl p-4 border" style="border-color:var(--border-color); background:var(--bg-card)">
                        <div>
                            <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $label }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $desc }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-9 h-5 rounded-full peer peer-checked:bg-indigo-600 transition" style="background:var(--border-color)"></div>
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full peer-checked:translate-x-4 transition"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Notification Email --}}
            <div class="pt-4 border-t" style="border-color:var(--border-color)">
                <h4 class="text-xs font-semibold mb-4" style="color:var(--text-secondary)">Notification Recipients</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Admin notification email</label>
                        <input type="email" value="admin@farmmantra.co.ug" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Finance notification email</label>
                        <input type="email" value="finance@farmmantra.co.ug" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection