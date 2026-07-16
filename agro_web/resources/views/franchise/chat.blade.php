@extends('layouts.app')
@section('title', 'Messages')
@section('page-title', 'Messages')

@section('content')
<div class="card-full overflow-hidden" style="height: calc(100vh - 180px); max-height: calc(100vh - 180px);">
    <div class="flex h-full" x-data="{ showSidebar: true }">
        {{-- Conversations Sidebar --}}
        <div class="w-full sm:w-72 flex-shrink-0 border-r flex flex-col transition-all duration-300"
             :class="{ 'hidden sm:flex': !showSidebar, 'absolute sm:relative inset-0 z-20 sm:z-auto bg-[var(--bg-card-solid)] sm:bg-transparent': showSidebar }"
             style="border-color:var(--border-color)">
            <div class="p-3 flex items-center gap-2" style="border-bottom:1px solid var(--border-color)">
                <button @click="showSidebar = false" class="sm:hidden p-1.5 rounded-lg" style="color:var(--text-muted)">
                    <i class="fas fa-arrow-left text-sm"></i>
                </button>
                <input type="text" placeholder="Search messages..." class="flex-1 rounded-lg border px-3 py-2 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
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
        <div class="flex-1 flex flex-col min-w-0">
            <div class="flex items-center gap-3 px-4 py-3 sm:hidden" style="border-bottom:1px solid var(--border-color)">
                <button @click="showSidebar = true" class="p-1.5 rounded-lg" style="color:var(--text-muted)">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <span class="text-sm font-semibold" style="color:var(--text-primary)">Messages</span>
            </div>
            <div class="flex-1 flex items-center justify-center" style="color:var(--text-muted)">
                <div class="text-center px-4">
                    <i class="fas fa-paper-plane text-4xl mb-3" style="color:var(--border-color)"></i>
                    <p class="text-sm font-medium">Select a conversation</p>
                    <p class="text-xs mt-1">Start chatting with your Farmmantra support team</p>
                </div>
            </div>
            <div class="p-3" style="border-top:1px solid var(--border-color)">
                <div class="flex gap-2">
                    <input type="text" placeholder="Type a message..." class="flex-1 rounded-lg border px-3 py-2.5 text-sm min-w-0" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary)">
                    <button class="px-4 py-2.5 gradient-indigo text-white rounded-lg text-sm font-semibold hover:opacity-90 transition flex-shrink-0">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
