@extends('layouts.app')
@section('title', 'My Inventory')
@section('page-title', 'Franchise Inventory')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Inventory ({{ $inventory->count() }} products)</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-right">Quantity</th>
                        <th class="px-4 py-3 text-right">Reorder Level</th>
                        <th class="px-4 py-3 text-right">Value</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $i)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--indigo)">{{ $i->product?->sku }}</td>
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $i->product?->name }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium" style="color:var(--text-primary)">{{ number_format($i->quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-muted)">{{ number_format($i->reorder_level) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-primary)">UGX {{ number_format($i->total_value) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $i->quantity <= $i->reorder_level ? 'badge-danger' : 'badge-success' }}">{{ $i->quantity <= $i->reorder_level ? 'Low Stock' : 'OK' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No inventory items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
