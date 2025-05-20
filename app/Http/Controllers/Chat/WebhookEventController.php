<?php
// app/Http/Controllers/Chat/WebhookEventController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\WebhookEvent;
use Illuminate\Http\Request;

class WebhookEventController extends Controller
{
    /**
     * Handle incoming webhook from Meta (or any provider).
     * Stores raw payload and event type.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_type' => 'required|string',
            'payload'    => 'required|array',
        ]);

        $event = WebhookEvent::create([
            'event_type' => $data['event_type'],
            'payload'    => $data['payload'],
            'received_at' => now(),
        ]);

        return response()->json($event, 201);
    }
}
