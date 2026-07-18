@extends('layouts.app')
@section('title', 'My Orders')
@section('page-title', 'My Orders')

@php
$products = \App\Models\Product::with('category')->where('is_active', true)->orderBy('name')->get();
@endphp

@section('content')
<div x-data="{ items: [{product_id: '', quantity: 1}], addItem() { this.items.push({product_id: '', quantity: 1}); }, removeItem(i) { this.items.splice(i, 1); } }">

    <div class="card-full mb-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Place New Order</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('web.franchise.orders.place') }}" method="POST">
                @csrf

                <template x-for="(item, index) in items" :key="index">
                    <div class="flex gap-3 items-end mb-3">
                        <div class="flex-1">
                            <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">Product</label>
                            <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                                <option value="">Select product</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) — UGX {{ number_format($p->standard_price) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-28">
                            <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">Qty</label>
                            <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="btn-delete mb-0.5" style="width:2rem;height:2rem"><i class="fas fa-trash-can text-xs"></i></button>
                    </div>
                </template>

                <button type="button" @click="addItem()" class="inline-flex items-center gap-1 text-xs font-medium mb-4" style="color:var(--accent)">
                    <i class="fas fa-plus"></i> Add Item
                </button>

                <div class="mb-4">
                    <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)" placeholder="Any special instructions..."></textarea>
                </div>

                <button type="submit" class="gradient-indigo px-6 py-2.5 rounded-lg text-sm font-semibold text-white">
                    <i class="fas fa-paper-plane mr-1"></i> Place Order
                </button>
            </form>
        </div>
    </div>

    <div class="card-full">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Order History ({{ $orders->total() }})</h3>
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

</div>
@endsection
