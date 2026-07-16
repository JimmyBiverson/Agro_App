@extends('layouts.app')
@section('title', 'Messages')
@section('page-title', 'Messages')

@section('content')
<div class="card-full" style="height: calc(100vh - 220px)">
    <div class="flex h-full">
        {{-- Conversations Sidebar --}}
        <div class="w-72 border-r flex flex-col" style="border-color:var(--border-color)">
            <div class="p-3" style="border-bottom:1px solid var(--border-color)">
                <input type="text" placeholder="Search messages..." class="w-full rounded-lg border px-3 py-2 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
            </div>
            <div class="flex-1 overflow-y-auto">
                <div class="p-4 text-center text-xs" style="color:var(--text-muted)">
                    <i class="fas fa-comments text-3xl mb-2 block" style="color:var(--border-color)"></i>
                    <p>No conversations yet</p>
                    <p class="mt-1">Messages with Farmmantra staff will appear here</p>
                </div>
            </div>
        </div>

        {{-- Chat Area --}}
        <div class="flex-1 flex flex-col">
            <div class="flex-1 flex items-center justify-center" style="color:var(--text-muted)">
                <div class="text-center">
                    <i class="fas fa-paper-plane text-4xl mb-3" style="color:var(--border-color)"></i>
                    <p class="text-sm font-medium">Select a conversation</p>
                    <p class="text-xs mt-1">Start chatting with your Farmmantra support team</p>
                </div>
            </div>
            <div class="p-3" style="border-top:1px solid var(--border-color)">
                <div class="flex gap-2">
                    <input type="text" placeholder="Type a message..." class="flex-1 rounded-lg border px-3 py-2.5 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    <button class="px-4 py-2.5 gradient-indigo text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
