@extends('layouts.app')
@section('title', 'News & Events')
@section('page-title', 'News & Events')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">News & Events ({{ $news->total() }})</h3>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Article
        </button>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Excerpt</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Published</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($news as $article)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $article->title }}</td>
                        <td class="px-4 py-3 text-sm max-w-xs truncate" style="color:var(--text-secondary)">{{ $article->excerpt }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $article->is_published ? 'badge-success' : 'badge-warning' }}">{{ $article->is_published ? 'Published' : 'Draft' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-muted)">{{ $article->published_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-indigo-400 hover:text-indigo-300 text-sm mr-2"><i class="fas fa-pen"></i></button>
                            <button class="text-red-400 hover:text-red-300 text-sm"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No articles yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $news->links() }}</div>
</div>
@endsection
