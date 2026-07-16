@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    {{-- Settings Sidebar --}}
    <div class="lg:col-span-1">
        <div class="card-full">
            <div class="card-body p-2">
                <nav class="space-y-1">
                    <a href="{{ route('web.admin.settings.general') }}" class="sidebar-link {{ request()->routeIs('web.admin.settings.general') ? 'active' : '' }}" style="margin:0">
                        <i class="fas fa-cog w-5 text-center text-sm"></i> General
                    </a>
                    <a href="{{ route('web.admin.settings.users') }}" class="sidebar-link {{ request()->routeIs('web.admin.settings.users') ? 'active' : '' }}" style="margin:0">
                        <i class="fas fa-users w-5 text-center text-sm"></i> User Management
                    </a>
                    <a href="{{ route('web.admin.settings.roles') }}" class="sidebar-link {{ request()->routeIs('web.admin.settings.roles') ? 'active' : '' }}" style="margin:0">
                        <i class="fas fa-shield-halved w-5 text-center text-sm"></i> Roles & Permissions
                    </a>
                    <a href="{{ route('web.admin.settings.system') }}" class="sidebar-link {{ request()->routeIs('web.admin.settings.system') ? 'active' : '' }}" style="margin:0">
                        <i class="fas fa-server w-5 text-center text-sm"></i> System Info
                    </a>
                </nav>
            </div>
        </div>
    </div>

    {{-- Settings Content --}}
    <div class="lg:col-span-3">
        <div class="card-full">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">General Settings</h3>
                    <p class="text-xs" style="color:var(--text-muted)">Configure your Farmmantra system</p>
                </div>
            </div>
            <div class="card-body">
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Company Name</label>
                            <input type="text" value="Farmmantra Agro Chemicals Limited" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">System Email</label>
                            <input type="email" value="info@farmmantra.co.ug" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Phone</label>
                            <input type="text" value="+256 700 000000" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Currency</label>
                            <input type="text" value="UGX (Ugandan Shilling)" disabled class="w-full rounded-lg border px-3 py-2.5 text-sm opacity-60" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Timezone</label>
                            <input type="text" value="Africa/Kampala (EAT)" disabled class="w-full rounded-lg border px-3 py-2.5 text-sm opacity-60" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Max Franchises</label>
                            <input type="number" value="50" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Company Address</label>
                        <textarea rows="2" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">Kampala, Uganda</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
