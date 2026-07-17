@extends('layouts.app')
@section('title', 'FAQs')
@section('page-title', 'Frequently Asked Questions')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- FAQ Form --}}
    <div class="lg:col-span-1">
        <div class="card-full">
            <div class="card-header">
                <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add / Edit FAQ</h3>
            </div>
            <div class="card-body">
                <form class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Question</label>
                        <input type="text" placeholder="e.g. How do I place an order?" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Answer</label>
                        <textarea rows="5" placeholder="Detailed answer..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Sort Order</label>
                        <input type="number" value="0" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" checked class="rounded border-gray-300">
                        <label for="is_active" class="text-xs font-medium" style="color:var(--text-secondary)">Active</label>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-save mr-1"></i> Save FAQ
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- FAQs List --}}
    <div class="lg:col-span-2">
        <div class="card-full">
            <div class="card-header">
                <h3 class="text-sm font-semibold" style="color:var(--text-primary)">All FAQs ({{ $faqs->count() }})</h3>
            </div>
            <div class="card-body space-y-3">
                @forelse($faqs as $faq)
                <div class="rounded-xl p-4 border" style="border-color:var(--border-color); background:var(--bg-card)">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-mono" style="color:var(--text-muted)">#{{ $faq->sort_order }}</span>
                                <span class="badge {{ $faq->is_active ? 'badge-success' : 'badge-danger' }}">{{ $faq->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <p class="text-sm font-semibold mb-1.5" style="color:var(--text-primary)">{{ $faq->question }}</p>
                            <p class="text-xs leading-relaxed" style="color:var(--text-secondary)">{{ Str::limit($faq->answer, 150) }}</p>
                        </div>
                        <div class="flex gap-1 flex-shrink-0">
                            <button class="text-indigo-400 hover:text-indigo-300 text-sm p-1"><i class="fas fa-pen"></i></button>
                            <button class="text-red-400 hover:text-red-300 text-sm p-1"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-circle-question text-3xl mb-3" style="color:var(--text-muted)"></i>
                    <p class="text-sm" style="color:var(--text-muted)">No FAQs yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
