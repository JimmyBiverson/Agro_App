@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Product Management')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Products ({{ $products->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Unit</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-right">Slabs</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--indigo)">{{ $p->sku }}</td>
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $p->name }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->category?->name }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->unit_of_measure }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold" style="color:var(--text-primary)">UGX {{ number_format($p->standard_price) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-primary)">{{ number_format($p->warehouseInventory->quantity ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-right" style="color:var(--text-muted)">{{ $p->priceSlabs->count() }} slabs</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $products->links() }}</div>
</div>
@endsection
