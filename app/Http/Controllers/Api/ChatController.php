<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Conversation::with(['latestMessage.sender', 'creator']);

        if ($user->role?->name === 'Franchise Partner') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('franchise_id', $user->franchise_id);
            });
        }

        $conversations = $query->latest()->paginate(20);
        return response()->json($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        $user = $request->user();

        $conversation = Conversation::create([
            'franchise_id' => $user->franchise_id,
            'created_by' => $user->id,
            'subject' => $request->subject,
            'priority' => $request->priority ?? 'normal',
            'status' => 'open',
        ]);

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Conversation started.', 'data' => $conversation], 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name === 'Franchise Partner') {
            if ($conversation->franchise_id !== $user->franchise_id && $conversation->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
        }
        $conversation->load(['messages.sender', 'creator', 'franchise']);
        return response()->json(['data' => $conversation]);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate(['message' => 'required|string']);

        $user = $request->user();
        if ($user->role?->name === 'Franchise Partner') {
            if ($conversation->franchise_id !== $user->franchise_id && $conversation->created_by !== $user->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Message sent.', 'data' => $message->load('sender')], 201);
    }
}
