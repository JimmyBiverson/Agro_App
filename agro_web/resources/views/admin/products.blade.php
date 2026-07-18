@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Product Management')

@section('content')
<div x-data="{ open: false }">
    <div class="card-full">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Products ({{ $products->total() }})</h3>
            <button @click="open = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Add Product
            </button>
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
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--accent)">{{ $p->sku }}</td>
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->category?->name }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->unit_of_measure }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold" style="color:var(--text-primary)">UGX {{ number_format($p->standard_price) }}</td>
                            <td class="px-4 py-3 text-sm text-right" style="color:var(--text-primary)">{{ number_format($p->warehouseInventory->quantity ?? 0) }}</td>
                            <td class="px-4 py-3 text-sm text-right" style="color:var(--text-muted)">{{ $p->priceSlabs->count() }} slabs</td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('web.admin.products.delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete product {{ addslashes($p->name) }}?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $p->id }}">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash-can text-xs"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No products found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $products->links() }}</div>
    </div>

    <!-- Add Product Modal -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay" style="display:none" @keydown.escape.window="open = false">
        <div class="modal-backdrop" @click="open = false"></div>
        <div class="modal-panel" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold" style="color:var(--text-primary)">Add New Product</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-muted)">Fill in the details below to add a product to inventory.</p>
                </div>
                <button @click="open = false" class="btn-delete" style="color:var(--text-muted);width:2rem;height:2rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('web.admin.products.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Product Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Roundup Gold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">SKU *</label>
                        <input type="text" name="sku" required placeholder="e.g. HER-001">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Category *</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Unit of Measure *</label>
                        <input type="text" name="unit_of_measure" required placeholder="e.g. Litre, Kg, Pack">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Selling Price (UGX) *</label>
                        <input type="number" name="selling_price" required min="0" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Standard Price (UGX) *</label>
                        <input type="number" name="standard_price" required min="0" placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Packaging Details</label>
                    <input type="text" name="packaging_details" placeholder="e.g. 1L bottle, 5kg bag">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Description</label>
                    <textarea name="description" rows="3" placeholder="Product description..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t" style="border-color:var(--border-color)">
                    <button type="button" @click="open = false" class="px-5 py-2.5 rounded-lg text-sm font-medium border transition hover:opacity-80" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25"><i class="fas fa-save mr-1.5"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
