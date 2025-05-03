<?php
// app/Http/Controllers/Chat/MessageController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Message;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use App\Models\Chat\Conversation;
use App\Models\Chat\Contact;
use App\Services\WhatsAppTokenService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MessageController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppTokenService $tokenService)
    {
        $this->whatsAppService = new WhatsAppService($tokenService);
    }

    /**
     * List all messages for a given conversation.
     */
    public function index($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('timestamp')
            ->get();

        return response()->json($messages);
    }

    /**
     * Store a new message in a conversation.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'from_me' => 'required|boolean',
                'message_id' => 'required|string',
                'message_type' => 'required|string',
                'content' => 'required|string',
                'conversation_id' => 'required|exists:conversations,id',
                'timestamp' => 'nullable|date',
            ]);

            $ts = isset($validated['timestamp'])
                ? Carbon::parse($validated['timestamp'])->utc()
                : now()->utc();
            $message = Message::create([
                'conversation_id' => $validated['conversation_id'],
                'from_me' => $validated['from_me'],
                'message_id' => $validated['message_id'],
                'client_message_id' => $validated['message_id'],
                'message_type' => $validated['message_type'],
                'content' => $validated['content'],
                'timestamp' => $ts
            ]);

            if ($validated['from_me']) {
                $conversation = $message->conversation;
                $contact = $conversation->contact;

                if (!$contact) {
                    $message->delete();
                    return response()->json([
                        'success' => false,
                        'error' => 'No se encontró el contacto asociado a la conversación'
                    ], 404);
                }

                $waResp = $this->whatsAppService->sendMessage(
                    $contact->wa_id,
                    $validated['content']
                );

                if ($waResp['success']) {
                    $message->update(['message_id' => $waResp['message_id'] ?? $validated['message_id']]);
                    Log::info('Mensaje enviado exitosamente', [
                        'message_id' => $message->id,
                        'whatsapp_message_id' => $waResp['message_id'] ?? null
                    ]);
                } else {
                    $message->delete();
                    Log::error('Error al enviar mensaje a WhatsApp', [
                        'message_id' => $message->id,
                        'status' => $waResp['status'],
                        'error' => $waResp['error']
                    ]);
                    $httpCode = 500;
                    $errorMsg = strtolower($waResp['error'] ?? '');
                    if (
                        $waResp['status'] == 401 ||
                        $waResp['status'] == 403 ||
                        strpos($errorMsg, 'missing permissions') !== false ||
                        strpos($errorMsg, 'does not exist') !== false
                    ) {
                        $httpCode = 401;
                    }
                    return response()->json([
                        'success' => false,
                        'error' => $waResp['error']
                    ], $httpCode);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error en MessageController@store', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar el mensaje'
            ], 500);
        }
    }

    /**
     * Guarda manualmente el token de WhatsApp enviado desde el frontend.
     */
    public function setToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        \App\Models\Chat\WhatsAppToken::deactivateAllTokens();
        \App\Models\Chat\WhatsAppToken::create([
            'token' => $request->token,
            'expires_at' => now()->addDays(60),
            'is_active' => true
        ]);

        return response()->json(['success' => true]);
    }
}
