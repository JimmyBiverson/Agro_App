@extends('layouts.app')
@section('title', 'Site Identity')
@section('page-title', 'Site Identity')

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
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Site Identity</h3>
            <p class="text-xs" style="color:var(--text-muted)">Control how your site looks to visitors</p>
        </div>
    </div>
    <div class="card-body">
        <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-xl p-5 border" style="border-color:var(--border-color); background:var(--bg-card)">
                    <label class="block text-xs font-semibold mb-3" style="color:var(--text-secondary)">Site Logo</label>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-16 w-24 rounded-lg gradient-indigo flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-lg">FM</span>
                        </div>
                        <div>
                            <p class="text-xs mb-1" style="color:var(--text-muted)">Current logo</p>
                            <p class="text-xs" style="color:var(--text-secondary)">Recommended: 200x60px, PNG/SVG</p>
                        </div>
                    </div>
                    <input type="file" accept="image/*" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div class="rounded-xl p-5 border" style="border-color:var(--border-color); background:var(--bg-card)">
                    <label class="block text-xs font-semibold mb-3" style="color:var(--text-secondary)">Favicon</label>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-10 w-10 rounded-lg gradient-indigo flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-xs">FM</span>
                        </div>
                        <div>
                            <p class="text-xs mb-1" style="color:var(--text-muted)">Current favicon</p>
                            <p class="text-xs" style="color:var(--text-secondary)">Recommended: 32x32px or 64x64px ICO/PNG</p>
                        </div>
                    </div>
                    <input type="file" accept="image/*,.ico" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Site Name</label>
                    <input type="text" value="Farmmantra" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Site Tagline</label>
                    <input type="text" value="Agro Chemicals Limited" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
            </div>

            <div class="pt-2 border-t" style="border-color:var(--border-color)">
                <h4 class="text-xs font-semibold mb-4" style="color:var(--text-secondary)">Contact Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Phone Number</label>
                        <input type="tel" value="+256 700 000000" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Email Address</label>
                        <input type="email" value="info@farmmantra.co.ug" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Physical Address</label>
                        <input type="text" value="Kampala, Uganda" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t" style="border-color:var(--border-color)">
                <h4 class="text-xs font-semibold mb-4" style="color:var(--text-secondary)">Social Media Links</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)"><i class="fab fa-facebook mr-1"></i> Facebook</label>
                        <input type="url" placeholder="https://facebook.com/..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)"><i class="fab fa-twitter mr-1"></i> Twitter / X</label>
                        <input type="url" placeholder="https://x.com/..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)"><i class="fab fa-instagram mr-1"></i> Instagram</label>
                        <input type="url" placeholder="https://instagram.com/..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)"><i class="fab fa-whatsapp mr-1"></i> WhatsApp</label>
                        <input type="tel" placeholder="+256..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-1"></i> Save Identity
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
