@extends('layouts.app')
@section('title', 'Warehouse Stock')
@section('page-title', 'Warehouse Stock')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Warehouse Inventory ({{ $stock->count() }} products)</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-left">Unit</th>
                        <th class="px-4 py-3 text-right">Quantity</th>
                        <th class="px-4 py-3 text-right">Reserved</th>
                        <th class="px-4 py-3 text-right">Available</th>
                        <th class="px-4 py-3 text-right">Reorder Level</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stock as $s)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--indigo)">{{ $s->product?->sku }}</td>
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $s->product?->name }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $s->product?->unit_of_measure }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium" style="color:var(--text-primary)">{{ number_format($s->quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-muted)">{{ number_format($s->reserved_quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-primary)">{{ number_format($s->quantity - $s->reserved_quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-muted)">{{ number_format($s->reorder_level) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $s->quantity <= $s->reorder_level ? 'badge-danger' : 'badge-success' }}">{{ $s->quantity <= $s->reorder_level ? 'Low Stock' : 'OK' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No warehouse stock found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
