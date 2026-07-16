@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1">
        <div class="card-full"><div class="card-body p-2">
            <nav class="space-y-1">
                <a href="{{ route('web.admin.settings.general') }}" class="sidebar-link" style="margin:0"><i class="fas fa-cog w-5 text-center text-sm"></i> General</a>
                <a href="{{ route('web.admin.settings.users') }}" class="sidebar-link" style="margin:0"><i class="fas fa-users w-5 text-center text-sm"></i> User Management</a>
                <a href="{{ route('web.admin.settings.roles') }}" class="sidebar-link active" style="margin:0"><i class="fas fa-shield-halved w-5 text-center text-sm"></i> Roles & Permissions</a>
                <a href="{{ route('web.admin.settings.system') }}" class="sidebar-link" style="margin:0"><i class="fas fa-server w-5 text-center text-sm"></i> System Info</a>
            </nav>
        </div></div>
    </div>
    <div class="lg:col-span-3">
        <div class="card-full">
            <div class="card-header"><h3 class="text-sm font-semibold" style="color:var(--text-primary)">System Roles</h3></div>
            <div class="card-body p-0">
                <table class="w-full table-dark">
                    <thead><tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-center">Users</th>
                        <th class="px-4 py-3 text-left">Description</th>
                    </tr></thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <td class="px-4 py-3"><span class="badge badge-primary">{{ $role->name }}</span></td>
                            <td class="px-4 py-3 text-sm text-center" style="color:var(--text-secondary)">{{ $role->users_count ?? $role->users()->count() }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-muted)">
                                @if($role->name === 'System Administrator') Full system access, user & data management
                                @elseif($role->name === 'Farmmantra Staff') Order approval, inventory monitoring
                                @elseif($role->name === 'Finance Department') Payment verification & reconciliation
                                @elseif($role->name === 'Franchise Partner') Orders, sales, inventory, payments
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
