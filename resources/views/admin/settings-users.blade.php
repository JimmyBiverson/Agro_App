@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1">
        <div class="card-full"><div class="card-body p-2">
            <nav class="space-y-1">
                <a href="{{ route('web.admin.settings.general') }}" class="sidebar-link" style="margin:0"><i class="fas fa-cog w-5 text-center text-sm"></i> General</a>
                <a href="{{ route('web.admin.settings.users') }}" class="sidebar-link active" style="margin:0"><i class="fas fa-users w-5 text-center text-sm"></i> User Management</a>
                <a href="{{ route('web.admin.settings.roles') }}" class="sidebar-link" style="margin:0"><i class="fas fa-shield-halved w-5 text-center text-sm"></i> Roles & Permissions</a>
                <a href="{{ route('web.admin.settings.system') }}" class="sidebar-link" style="margin:0"><i class="fas fa-server w-5 text-center text-sm"></i> System Info</a>
            </nav>
        </div></div>
    </div>
    <div class="lg:col-span-3 space-y-4">
        {{-- User Stats --}}
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
                <table class="w-full table-dark">
                    <thead>
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Franchise</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Joined</th>
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
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm" style="color:var(--text-muted)">No users found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
