@extends('layouts.app')
@section('title', 'My Orders')
@section('page-title', 'My Orders')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Orders ({{ $orders->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Order</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Delivery Date</th>
                        <th class="px-4 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--accent)">{{ $o->order_number }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $o->items->pluck('product.name')->implode(', ') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-right" style="color:var(--text-primary)">UGX {{ number_format($o->total_amount) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($o->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                            @elseif($o->status === 'approved')
                            <span class="badge badge-info">Approved</span>
                            @elseif($o->status === 'declined')
                            <span class="badge badge-danger">Declined</span>
                            @else
                            <span class="badge badge-success">Delivered</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $o->expected_delivery_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $o->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $orders->links() }}</div>
</div>
@endsection
