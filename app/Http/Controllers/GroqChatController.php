<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|string',
            'messages.*.content' => 'required|string',
        ]);

        $apiKey = env('GROQ_API');
        if (!$apiKey) {
            return response()->json(['error' => 'API Key no configurada en .env'], 500);
        }

        $payload = [
            'messages' => $request->input('messages'),
            'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
            'temperature' => 1,
            'max_completion_tokens' => 1024,
            'top_p' => 1,
            'stream' => false,
            'stop' => null,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post('https://api.groq.com/openai/v1/chat/completions', $payload);

        // Logging de interacción por chat_id
        try {
            $logDir = storage_path('app/chatbot_logs');
            if (!is_dir($logDir)) {
                mkdir($logDir, 0775, true);
            }
            $chatId = $request->input('chat_id');
            if (!$chatId) {
                $chatId = 'anon_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
            }
            $logFile = $logDir . '/chat_' . $chatId . '.txt';
            $ip = $request->ip();
            $user = auth()->check() ? auth()->user()->email : 'anonimo';
            $fecha = date('Y-m-d H:i:s');
            $historial = print_r($request->input('messages'), true);
            $respuesta = $response->successful() ? print_r($response->json(), true) : $response->body();
            $logText = "==== Conversación - $fecha ====" . PHP_EOL .
                "IP: $ip\nUsuario: $user\nChat ID: $chatId\n" .
                "Historial enviado:\n$historial" .
                "Respuesta modelo:\n$respuesta" . PHP_EOL .
                str_repeat('-', 60) . PHP_EOL;
            file_put_contents($logFile, $logText, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            Log::error('No se pudo guardar log de chatbot', ['error' => $e->getMessage()]);
        }

        if ($response->failed()) {
            Log::error('Groq API error', ['body' => $response->body()]);
            return response()->json(['error' => 'Error al comunicarse con Groq API', 'details' => $response->body()], 500);
        }

        return response()->json($response->json());
    }
}
