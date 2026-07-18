@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')

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

<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card-stat text-center">
            <p class="text-2xl font-bold" style="color:var(--text-primary)">{{ $users->total() }}</p>
            <p class="text-xs mt-1" style="color:var(--text-muted)">Total Users</p>
        </div>
        <div class="card-stat text-center">
            <p class="text-2xl font-bold text-emerald-500">{{ $users->where('is_active', true)->count() }}</p>
            <p class="text-xs mt-1" style="color:var(--text-muted)">Active</p>
        </div>
        <div class="card-stat text-center">
            <p class="text-2xl font-bold text-red-500">{{ $users->where('is_active', false)->count() }}</p>
            <p class="text-xs mt-1" style="color:var(--text-muted)">Deactivated</p>
        </div>
    </div>

    <div class="card-full">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">All Users</h3>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Franchise</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Joined</th>
                        <th class="px-4 py-3 text-center no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full gradient-indigo flex items-center justify-center text-white text-xs font-bold">{{ substr($u->name, 0, 1) }}</div>
                                <div>
                                    <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $u->name }}</p>
                                    <p class="text-xs" style="color:var(--text-muted)">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3"><span class="badge badge-primary">{{ $u->role?->name ?? 'N/A' }}</span></td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $u->franchise?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($u->is_active)
                            <span class="badge badge-success">Active</span>
                            @else
                            <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $u->created_at?->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-center no-print">
                            <button onclick="resetPassword({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition border hover:bg-amber-500/10"
                                    style="border-color:var(--border-color); color:var(--warning)">
                                <i class="fas fa-key mr-1"></i>Reset
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm" style="color:var(--text-muted)">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="px-4 py-3 overflow-x-auto" style="border-top:1px solid var(--border-color)">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetPassword(userId, userName) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
        <div class="modal-panel" style="max-width:440px; margin-top:10vh;">
            <div style="padding:24px;">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:4px; color:var(--text-primary)">Reset Password</h3>
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">Set a new password for <strong>${userName}</strong></p>
                <form method="POST" action="{{ route('web.admin.users.resetPassword') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="${userId}">
                    <div style="margin-bottom:16px;">
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">New Password</label>
                        <input type="text" name="new_password" required minlength="8"
                               class="w-full rounded-xl border px-3 py-2.5 text-sm" placeholder="Min 8 characters"
                               style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="this.closest('.modal-overlay').remove()"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold transition border"
                                style="border-color:var(--border-color); color:var(--text-muted); background:var(--bg-card)">Cancel</button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition gradient-indigo hover:opacity-90">
                            <i class="fas fa-key mr-1.5"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>
@endpush
