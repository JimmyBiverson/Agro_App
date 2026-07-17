@extends('layouts.app')
@section('title', 'My Sales')
@section('page-title', 'Sales History')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Sales ({{ $sales->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Sale #</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-center">Payment</th>
                        <th class="px-4 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--accent)">{{ $s->sale_number }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $s->customer?->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $s->items->pluck('product.name')->implode(', ') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-right" style="color:var(--text-primary)">UGX {{ number_format($s->final_amount) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $s->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($s->payment_status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $s->sale_date->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No sales recorded yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $sales->links() }}</div>
</div>
@endsection
