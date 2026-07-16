@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Users ({{ $users->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Franchise</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $u->email }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $u->role?->name }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $u->franchise?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $users->links() }}</div>
</div>
@endsection
