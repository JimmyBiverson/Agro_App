@extends('layouts.app')
@section('title', 'Franchises')
@section('page-title', 'Franchise Management')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Franchises ({{ $franchises->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Region</th>
                        <th class="px-4 py-3 text-left">Contact</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($franchises as $f)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--indigo)">{{ $f->code }}</td>
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $f->name }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $f->region }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $f->contact_person }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold" style="color:var(--text-primary)">UGX {{ number_format($f->account_balance) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $f->is_active ? 'badge-success' : 'badge-danger' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No franchises found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $franchises->links() }}</div>
</div>
@endsection
