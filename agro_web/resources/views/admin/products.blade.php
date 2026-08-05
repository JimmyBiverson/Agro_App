@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Product Management')

@section('content')
<div x-data="{ open: false, openSlab: false, openTarget: false, editing: false, editProduct: {}, editingSlab: false, editSlab: {}, editingTarget: false, editTarget: {} }">
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
                            <th class="px-4 py-3 text-left">Image</th>
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
                            <td class="px-4 py-3">
                                @if($p->image_url)
                                <img src="{{ $p->image_url }}" alt="{{ $p->name }}" style="width:3rem;height:3rem;border-radius:0.5rem;object-fit:cover;border:1px solid var(--border-color);flex-shrink:0;display:block" loading="lazy">
                                @else
                                <div style="width:3rem;height:3rem;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;background:var(--bg-input);color:var(--text-muted);flex-shrink:0"><i class="fas fa-flask text-xs"></i></div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--accent)">{{ $p->sku }}</td>
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->category?->name }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->unit_of_measure }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold" style="color:var(--text-primary)">UGX {{ number_format($p->standard_price) }}</td>
                            <td class="px-4 py-3 text-sm text-right" style="color:var(--text-primary)">{{ number_format($p->warehouseInventory->quantity ?? 0) }}</td>
                            <td class="px-4 py-3 text-sm text-right" style="color:var(--text-muted)">{{ $p->priceSlabs->count() }} slabs</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="btn-action" title="Edit" style="color:var(--accent); background:rgba(99,102,241,0.12)"
                                        @click="editProduct = {{ \Illuminate\Support\Js::from(['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'category_id' => $p->category_id ? (string) $p->category_id : '', 'unit_of_measure' => $p->unit_of_measure, 'selling_price' => (string) $p->selling_price, 'standard_price' => (string) $p->standard_price, 'packaging_details' => (string) $p->packaging_details, 'description' => (string) $p->description, 'image_url' => $p->image_url, 'gallery_images' => $p->images->map(fn($img) => ['id' => $img->id, 'url' => $img->image_url]), 'is_active' => (bool) $p->is_active]) }}; editing = true">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="{{ route('web.admin.products.delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete product {{ addslashes($p->name) }}?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $p->id }}">
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash-can text-xs"></i></button>
                                    </form>
                                </div>
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

    <div class="card-full mt-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Price Slabs</h3>
            <button @click="openSlab = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Add Slab
            </button>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="w-full table-dark">
                    <thead>
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <th class="px-4 py-3 text-left">Product</th>
                            <th class="px-4 py-3 text-right">Min Qty</th>
                            <th class="px-4 py-3 text-right">Max Qty</th>
                            <th class="px-4 py-3 text-right">Slab Price</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $slabs = \App\Models\PriceSlab::with('product:id,name,sku')->latest()->get(); @endphp
                        @forelse($slabs as $slab)
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $slab->product?->name }} ({{ $slab->product?->sku }})</td>
                            <td class="px-4 py-3 text-sm text-right" style="color:var(--text-secondary)">{{ $slab->min_quantity }}</td>
                            <td class="px-4 py-3 text-sm text-right" style="color:var(--text-secondary)">{{ $slab->max_quantity ?? '∞' }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold" style="color:var(--text-primary)">UGX {{ number_format($slab->slab_price) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="btn-action" title="Edit" style="color:var(--accent); background:rgba(99,102,241,0.12)"
                                        @click="editSlab = {{ \Illuminate\Support\Js::from(['id' => $slab->id, 'product_id' => (string) $slab->product_id, 'min_quantity' => (string) $slab->min_quantity, 'max_quantity' => $slab->max_quantity ? (string) $slab->max_quantity : '', 'slab_price' => (string) $slab->slab_price]) }}; editingSlab = true">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="{{ route('web.admin.priceSlabs.delete') }}" method="POST" class="inline" onsubmit="return confirm('Remove this price slab?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $slab->id }}">
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash-can text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No price slabs configured</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card-full mt-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Sales Targets</h3>
            <button @click="openTarget = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Set Target
            </button>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="w-full table-dark">
                    <thead>
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <th class="px-4 py-3 text-left">Franchise</th>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-center">Month</th>
                            <th class="px-4 py-3 text-right">Target</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $targets = \App\Models\SalesTarget::with(['franchise:id,name', 'productCategory:id,name'])->latest()->get(); @endphp
                        @forelse($targets as $t)
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $t->franchise?->name ?? 'All' }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $t->productCategory?->name ?? 'All Products' }}</td>
                            <td class="px-4 py-3 text-sm text-center" style="color:var(--text-secondary)">{{ $t->month }}/{{ $t->year }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold" style="color:var(--text-primary)">UGX {{ number_format($t->target_amount) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="btn-action" title="Edit" style="color:var(--accent); background:rgba(99,102,241,0.12)"
                                        @click="editTarget = {{ \Illuminate\Support\Js::from(['id' => $t->id, 'franchise_id' => (string) $t->franchise_id, 'product_category_id' => $t->product_category_id ? (string) $t->product_category_id : '', 'month' => (string) $t->month, 'year' => (string) $t->year, 'target_amount' => (string) $t->target_amount]) }}; editingTarget = true">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="{{ route('web.admin.salesTargets.delete') }}" method="POST" class="inline" onsubmit="return confirm('Remove this sales target?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $t->id }}">
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash-can text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No sales targets set</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
            <form action="{{ route('web.admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Primary Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Additional Gallery Images</label>
                        <input type="file" name="images[]" multiple accept="image/*" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
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

    <!-- Edit Product Modal -->
    <div x-show="editing" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay" style="display:none" @keydown.escape.window="editing = false">
        <div class="modal-backdrop" @click="editing = false"></div>
        <div class="modal-panel" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold" style="color:var(--text-primary)">Edit Product</h3>
                    <p class="text-xs mt-0.5" style="color:var(--text-muted)">Update the product details below.</p>
                </div>
                <button @click="editing = false" class="btn-delete" style="color:var(--text-muted);width:2rem;height:2rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('web.admin.products.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="id" :value="editProduct.id">
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Replace Primary Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full rounded-lg border px-3 py-2 text-xs" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Add Gallery Images</label>
                            <input type="file" name="images[]" multiple accept="image/*" class="w-full rounded-lg border px-3 py-2 text-xs" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        </div>
                    </div>
                    <template x-if="editProduct.gallery_images && editProduct.gallery_images.length > 0">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Existing Gallery Images (Check to delete)</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="img in editProduct.gallery_images" :key="img.id">
                                    <label class="relative block group cursor-pointer" style="width:3.5rem;height:3.5rem">
                                        <input type="checkbox" name="delete_images[]" :value="img.id" class="peer hidden" @change="$el.nextElementSibling.style.opacity = $el.checked ? '0.2' : '1'">
                                        <img :src="img.url" class="w-full h-full object-cover rounded-lg border transition" style="border-color:var(--border-color)">
                                        <div class="absolute inset-0 bg-red-600/80 rounded-lg flex items-center justify-center text-white text-xs opacity-0 peer-checked:opacity-100 transition">
                                            <i class="fas fa-trash-can"></i>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Product Name *</label>
                        <input type="text" name="name" required x-model="editProduct.name">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">SKU *</label>
                        <input type="text" name="sku" required x-model="editProduct.sku">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Category *</label>
                        <select name="category_id" required x-model="editProduct.category_id">
                            <option value="">Select category</option>
                            @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Unit of Measure *</label>
                        <input type="text" name="unit_of_measure" required x-model="editProduct.unit_of_measure">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Selling Price (UGX) *</label>
                        <input type="number" name="selling_price" required min="0" x-model="editProduct.selling_price">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Standard Price (UGX) *</label>
                        <input type="number" name="standard_price" required min="0" x-model="editProduct.standard_price">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Packaging Details</label>
                    <input type="text" name="packaging_details" x-model="editProduct.packaging_details">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Description</label>
                    <textarea name="description" rows="3" x-model="editProduct.description"></textarea>
                </div>
                <div class="flex items-center justify-between pt-2 border-t" style="border-color:var(--border-color)">
                    <label class="flex items-center gap-2 text-sm cursor-pointer" style="color:var(--text-secondary)">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" :checked="!!editProduct.is_active" @change="editProduct.is_active = $event.target.checked">
                        Product active
                    </label>
                    <div class="flex gap-3">
                        <button type="button" @click="editing = false" class="px-5 py-2.5 rounded-lg text-sm font-medium border transition hover:opacity-80" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25"><i class="fas fa-save mr-1.5"></i> Update Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Price Slab Modal -->
    <div x-show="openSlab" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay" style="display:none" @keydown.escape.window="openSlab = false">
        <div class="modal-backdrop" @click="openSlab = false"></div>
        <div class="modal-panel" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold" style="color:var(--text-primary)">Add Price Slab</h3>
                <button @click="openSlab = false" class="btn-delete" style="color:var(--text-muted);width:2rem;height:2rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('web.admin.priceSlabs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Product *</label>
                    <select name="product_id" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                        <option value="">Select product</option>
                        @foreach(\App\Models\Product::orderBy('name')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Min Qty *</label>
                        <input type="number" name="min_qty" required min="1" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Max Qty</label>
                        <input type="number" name="max_qty" min="1" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Slab Price (UGX) *</label>
                        <input type="number" name="slab_price" required min="0" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t" style="border-color:var(--border-color)">
                    <button type="button" @click="openSlab = false" class="px-5 py-2.5 rounded-lg text-sm font-medium border transition" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-save mr-1.5"></i> Save Slab</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Price Slab Modal -->
    <div x-show="editingSlab" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay" style="display:none" @keydown.escape.window="editingSlab = false">
        <div class="modal-backdrop" @click="editingSlab = false"></div>
        <div class="modal-panel" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold" style="color:var(--text-primary)">Edit Price Slab</h3>
                <button @click="editingSlab = false" class="btn-delete" style="color:var(--text-muted);width:2rem;height:2rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('web.admin.priceSlabs.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id" :value="editSlab.id">
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Product *</label>
                    <select name="product_id" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)" x-model="editSlab.product_id">
                        <option value="">Select product</option>
                        @foreach(\App\Models\Product::orderBy('name')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Min Qty *</label>
                        <input type="number" name="min_qty" required min="1" x-model="editSlab.min_quantity" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Max Qty</label>
                        <input type="number" name="max_qty" min="1" x-model="editSlab.max_quantity" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Slab Price (UGX) *</label>
                        <input type="number" name="slab_price" required min="0" x-model="editSlab.slab_price" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t" style="border-color:var(--border-color)">
                    <button type="button" @click="editingSlab = false" class="px-5 py-2.5 rounded-lg text-sm font-medium border transition" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-save mr-1.5"></i> Update Slab</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Sales Target Modal -->
    <div x-show="openTarget" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay" style="display:none" @keydown.escape.window="openTarget = false">
        <div class="modal-backdrop" @click="openTarget = false"></div>
        <div class="modal-panel" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold" style="color:var(--text-primary)">Set Sales Target</h3>
                <button @click="openTarget = false" class="btn-delete" style="color:var(--text-muted);width:2rem;height:2rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('web.admin.salesTargets.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Franchise *</label>
                        <select name="franchise_id" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                            <option value="">Select franchise</option>
                            @foreach(\App\Models\Franchise::where('is_active', true)->orderBy('name')->get() as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Category (blank = all)</label>
                        <select name="product_category_id" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                            <option value="">All Products</option>
                            @foreach(\App\Models\Category::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Month *</label>
                        <select name="month" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Year *</label>
                        <input type="number" name="year" value="{{ date('Y') }}" required min="2024" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Target (UGX) *</label>
                        <input type="number" name="target_amount" required min="0" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t" style="border-color:var(--border-color)">
                    <button type="button" @click="openTarget = false" class="px-5 py-2.5 rounded-lg text-sm font-medium border transition" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-save mr-1.5"></i> Save Target</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Sales Target Modal -->
    <div x-show="editingTarget" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay" style="display:none" @keydown.escape.window="editingTarget = false">
        <div class="modal-backdrop" @click="editingTarget = false"></div>
        <div class="modal-panel" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold" style="color:var(--text-primary)">Edit Sales Target</h3>
                <button @click="editingTarget = false" class="btn-delete" style="color:var(--text-muted);width:2rem;height:2rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('web.admin.salesTargets.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id" :value="editTarget.id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Franchise *</label>
                        <select name="franchise_id" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)" x-model="editTarget.franchise_id">
                            <option value="">Select franchise</option>
                            @foreach(\App\Models\Franchise::where('is_active', true)->orderBy('name')->get() as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Category (blank = all)</label>
                        <select name="product_category_id" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)" x-model="editTarget.product_category_id">
                            <option value="">All Products</option>
                            @foreach(\App\Models\Category::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Month *</label>
                        <select name="month" required class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)" x-model="editTarget.month">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Year *</label>
                        <input type="number" name="year" required min="2024" x-model="editTarget.year" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Target (UGX) *</label>
                        <input type="number" name="target_amount" required min="0" x-model="editTarget.target_amount" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t" style="border-color:var(--border-color)">
                    <button type="button" @click="editingTarget = false" class="px-5 py-2.5 rounded-lg text-sm font-medium border transition" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-save mr-1.5"></i> Update Target</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
