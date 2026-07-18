@extends('layouts.app')
@section('title', 'My Sales')
@section('page-title', 'Sales')

@php
    $products = \App\Models\Product::where('is_active', true)->orderBy('name')->get();
    $invItems = \App\Models\FranchiseInventory::where('franchise_id', auth()->user()->franchise_id)->with('product')->get();
    $customers = \App\Models\Customer::where('franchise_id', auth()->user()->franchise_id)->get();
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Record New Sale --}}
    <div class="card-full" x-data="saleForm()">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">
                <i class="fas fa-cart-plus mr-2" style="color:var(--accent)"></i>Record New Sale
            </h3>
        </div>
        <div class="card-body">
            @if($errors->any())
            <div class="mb-4 p-3 rounded-xl text-sm font-medium" style="background:rgba(239,68,68,0.08); color:var(--danger); border:1px solid rgba(239,68,68,0.15)">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('web.franchise.sales.create') }}">
                @csrf

                <div class="space-y-4">

                    {{-- Customer --}}
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Customer (optional)</label>
                        <select name="customer_id" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary); appearance:none; -webkit-appearance:none; background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E&quot;); background-repeat:no-repeat; background-position:right 0.75rem center; padding-right:2rem;">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sale Items --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-medium" style="color:var(--text-secondary)">Sale Items</label>
                            <button type="button" @click="addItem()" class="text-xs font-semibold px-2.5 py-1 rounded-lg transition" style="color:var(--accent); background:var(--accent-light)">
                                <i class="fas fa-plus mr-1"></i>Add Item
                            </button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex items-start gap-2 p-3 rounded-xl border" style="border-color:var(--border-color); background:var(--bg-input)">
                                    <div class="flex-1 space-y-2">
                                        <div>
                                            <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="updateStock(index)" required
                                                class="w-full rounded-lg border px-3 py-2 text-sm" style="background:var(--bg-card-solid); border-color:var(--border-color); color:var(--text-primary); appearance:none; -webkit-appearance:none; background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E&quot;); background-repeat:no-repeat; background-position:right 0.75rem center; padding-right:2rem;">
                                                <option value="">Select product...</option>
                                                @foreach($invItems as $inv)
                                                <option value="{{ $inv->product_id }}" data-stock="{{ $inv->quantity }}">{{ $inv->product?->name }} — Stock: {{ $inv->quantity }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" min="1" :max="item.max_stock" required placeholder="Qty"
                                                    class="w-full rounded-lg border px-3 py-2 text-sm" style="background:var(--bg-card-solid); border-color:var(--border-color); color:var(--text-primary)">
                                            </div>
                                            <span class="text-[11px] font-medium whitespace-nowrap" style="color:var(--text-muted)">
                                                Stock: <span x-text="item.max_stock" class="font-bold" style="color:var(--text-secondary)"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="btn-delete mt-1" x-show="items.length > 1">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Payment & Discount row --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Payment Method</label>
                            <select name="payment_method" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary); appearance:none; -webkit-appearance:none; background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E&quot;); background-repeat:no-repeat; background-position:right 0.75rem center; padding-right:2rem;">
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Discount (UGX)</label>
                            <input type="number" name="discount" value="0" min="0" placeholder="0"
                                class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Notes (optional)</label>
                        <textarea name="notes" rows="2" placeholder="Any additional notes..."
                            class="w-full rounded-lg border px-3 py-2.5 text-sm resize-none" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)"></textarea>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 4px 15px rgba(99,102,241,0.35);">
                        <i class="fas fa-check mr-2"></i>Record Sale
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Customer --}}
    <div class="card-full">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">
                <i class="fas fa-user-plus mr-2" style="color:var(--success)"></i>Add Customer
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('web.franchise.customers.create') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Full Name *</label>
                        <input type="text" name="name" required placeholder="Customer name"
                            class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Phone</label>
                            <input type="text" name="phone" placeholder="+256 7XX XXX XXX"
                                class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Email</label>
                            <input type="email" name="email" placeholder="email@example.com"
                                class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Address</label>
                        <input type="text" name="address" placeholder="Location / address"
                            class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_wholesale" value="1" id="is_wholesale"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_wholesale" class="text-sm font-medium" style="color:var(--text-secondary)">Wholesale Customer</label>
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all" style="background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 4px 15px rgba(16,185,129,0.35);">
                        <i class="fas fa-user-plus mr-2"></i>Add Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Sales History --}}
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Sales History ({{ $sales->total() }})</h3>
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

@push('scripts')
<script>
function saleForm() {
    return {
        items: [{ product_id: '', quantity: 1, max_stock: 0 }],
        addItem() {
            this.items.push({ product_id: '', quantity: 1, max_stock: 0 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        updateStock(index) {
            var sel = document.querySelectorAll('select[name="items['+index+'][product_id]"]')[0];
            if (!sel) return;
            var opt = sel.options[sel.selectedIndex];
            this.items[index].max_stock = parseInt(opt.dataset.stock) || 0;
            if (this.items[index].quantity > this.items[index].max_stock) {
                this.items[index].quantity = this.items[index].max_stock || 1;
            }
        }
    }
}
</script>
@endpush
@endsection
