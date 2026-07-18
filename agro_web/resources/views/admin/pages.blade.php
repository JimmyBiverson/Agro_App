@extends('layouts.app')
@section('title', 'Pages')
@section('page-title', 'CMS Pages')

@section('content')
<div x-data="{ open: false }">
    <div class="card-full">
        <div class="card-header">
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Pages ({{ $pages->total() }})</h3>
            <button @click="open = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Add Page
            </button>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="w-full table-dark">
                    <thead>
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Slug</th>
                            <th class="px-4 py-3 text-left">Meta Description</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                        <tr class="border-b" style="border-color:var(--border-color)">
                            <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $page->title }}</td>
                            <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">/{{ $page->slug }}</td>
                            <td class="px-4 py-3 text-sm max-w-xs truncate" style="color:var(--text-muted)">{{ $page->meta_description ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge {{ $page->is_published ? 'badge-success' : 'badge-warning' }}">{{ $page->is_published ? 'Published' : 'Draft' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('web.admin.pages.delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete page {{ addslashes($page->title) }}?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $page->id }}">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No pages yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $pages->links() }}</div>
    </div>

    <!-- Add Page Modal -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none" @keydown.escape.window="open = false">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border p-6 shadow-2xl max-h-[90vh] overflow-y-auto" style="background:var(--bg-card); border-color:var(--border-color)" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold" style="color:var(--text-primary)">Add Page</h3>
                <button @click="open = false" class="text-sm" style="color:var(--text-muted)"><i class="fas fa-times text-lg"></i></button>
            </div>
            <form action="{{ route('web.admin.pages.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Title *</label>
                    <input type="text" name="title" required placeholder="Page title" class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Body</label>
                    <textarea name="body" rows="8" placeholder="Page content..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:var(--text-secondary)">Meta Description</label>
                    <input type="text" name="meta_description" placeholder="SEO description..." class="w-full rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-lg text-sm font-medium border" style="border-color:var(--border-color); color:var(--text-secondary)">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-save mr-1"></i> Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
