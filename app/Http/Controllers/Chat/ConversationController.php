<?php
// app/Http/Controllers/Chat/ConversationController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * List all conversations, ordered by last message timestamp.
     */
    public function index()
    {
        $conversations = Conversation::with('contact')
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    /**
     * Show a single conversation with its contact and messages.
     */
    public function show($id)
    {
        $conversation = Conversation::with(['contact', 'messages'])
            ->findOrFail($id);

        return response()->json($conversation);
    }
}
