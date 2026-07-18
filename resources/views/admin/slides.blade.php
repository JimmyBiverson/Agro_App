@extends('layouts.app')
@section('title', 'Slides / Banners')
@section('page-title', 'Slides & Banners')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card-full">
            <div class="card-header">
                <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add Slide</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('web.admin.slides.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Title *</label>
                        <input type="text" name="title" required placeholder="Banner headline" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Subtitle</label>
                        <input type="text" name="subtitle" placeholder="Supporting text" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Button Text</label>
                        <input type="text" name="button_text" placeholder="e.g. Shop Now" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Button URL</label>
                        <input type="url" name="button_url" placeholder="https://..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-secondary)">Sort Order</label>
                        <input type="number" name="sort_order" value="0" min="0" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-save mr-1"></i> Save Slide
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="card-full">
            <div class="card-header">
                <h3 class="text-sm font-semibold" style="color:var(--text-primary)">All Slides ({{ $slides->count() }})</h3>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full table-dark">
                        <thead>
                            <tr class="border-b" style="border-color:var(--border-color)">
                                <th class="px-4 py-3 text-left">Order</th>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Button</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slides as $slide)
                            <tr class="border-b" style="border-color:var(--border-color)">
                                <td class="px-4 py-3 text-sm" style="color:var(--text-muted)">{{ $slide->sort_order }}</td>
                                <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $slide->title }}</td>
                                <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $slide->button_text ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge {{ $slide->is_active ? 'badge-success' : 'badge-danger' }}">{{ $slide->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('web.admin.slides.delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete slide {{ addslashes($slide->title) }}?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $slide->id }}">
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash-can text-xs"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No slides yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
