@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div x-data="{ open: false }">
    <div class="card-full">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Users ({{ $users->total() }})</h3>
            <button @click="open = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Add User
            </button>
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
                            <th class="px-4 py-3 text-center">Actions</th>
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
                            <td class="px-4 py-3 text-center">
                                @if(auth()->id() !== $u->id)
                                <form action="{{ route('web.admin.users.delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete user {{ addslashes($u->name) }}?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $u->id }}">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm"><i class="fas fa-trash"></i></button>
                                </form>
                                @else
                                <span class="text-xs" style="color:var(--text-muted)">You</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No users found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $users->links() }}</div>
    </div>

    <!-- Add User Modal -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none" @keydown.escape.window="open = false">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border p-6 shadow-2xl max-h-[90vh] overflow-y-auto" style="background:var(--bg-card); border-color:var(--border-color)" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold" style="color:var(--text-primary)">Add New User</h3>
                <button @click="open = false" class="text-sm" style="color:var(--text-muted)"><i class="fas fa-times text-lg"></i></button>
            </div>
            <form action="{{ route('web.admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. John Okello" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Email *</label>
                    <input type="email" name="email" required placeholder="user@example.com" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="Min 6 characters" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Role *</label>
                        <select name="role_id" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                            <option value="">Select role</option>
                            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Franchise</label>
                        <select name="franchise_id" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                            <option value="">None</option>
                            @foreach(\App\Models\Franchise::orderBy('name')->get() as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Phone</label>
                    <input type="text" name="phone" placeholder="+256..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-lg text-sm font-medium border" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-save mr-1"></i> Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
