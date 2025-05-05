<?php
// app/Http/Controllers/Chat/WebhookController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Chat\{Contact, Conversation, Message, WebhookEvent};

class WebhookController extends Controller
{
    /*--------- 1) Hand-shake de verificación (GET) ---------*/
    public function verify(Request $request)
    {
        $mode      = $request->input('hub_mode');
        $token     = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        Log::info('WA Verify - Inicio de verificación', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge,
            'query_params' => $request->query()
        ]);

        if ($mode === 'subscribe' && $token === env('WHATSAPP_VERIFY_TOKEN')) {
            Log::info('WA Verify - Verificación exitosa');
            return response($challenge, 200);
        }

        Log::warning('WA Verify - Verificación fallida', [
            'mode' => $mode,
            'token' => $token,
            'expected_token' => env('WHATSAPP_VERIFY_TOKEN')
        ]);
        return response('Forbidden', 403);
    }

    /*--------- 2) Webhook POST (mensajes + statuses) ---------*/
    public function receive(Request $request)
    {
        Log::info('Webhook recibido - Inicio', [
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        // Imprimir el JSON completo en el log
        Log::info('JSON completo recibido:', [
            'json' => json_encode($request->all(), JSON_PRETTY_PRINT)
        ]);

        // Guarda todo por auditoría
        $webhookEvent = WebhookEvent::create([
            'event_type'  => 'messages',
            'payload'     => $request->all(),
            'received_at' => now(),
        ]);

        Log::info('WebhookEvent creado', ['event_id' => $webhookEvent->id]);

        $businessWaId = env('WHATSAPP_BUSINESS_WAID');   // tu número en formato E.164
        Log::info('Business WA ID configurado', ['wa_id' => $businessWaId]);

        foreach ($request->input('entry', []) as $entry) {
            Log::info('Procesando entrada', [
                'entry_id' => $entry['id'] ?? 'unknown',
                'changes_count' => count($entry['changes'] ?? [])
            ]);

            foreach ($entry['changes'] ?? [] as $change) {
                Log::info('Procesando cambio', [
                    'field' => $change['field'] ?? 'unknown',
                    'value' => array_keys($change['value'] ?? [])
                ]);

                /*-- Procesa mensajes --*/
                foreach ($change['value']['messages'] ?? [] as $m) {
                    Log::info('Procesando mensaje', [
                        'message_id' => $m['id'] ?? 'unknown',
                        'type' => $m['type'] ?? 'unknown',
                        'from' => $m['from'] ?? 'unknown',
                        'to' => $m['to'] ?? 'unknown',
                        'timestamp' => $m['timestamp'] ?? 'unknown'
                    ]);

                    $isOutgoing = ($m['from'] ?? '') === $businessWaId;
                    $waId       = $isOutgoing ? ($m['to'] ?? null) : ($m['from'] ?? null);

                    Log::info('Datos del mensaje procesado', [
                        'is_outgoing' => $isOutgoing,
                        'wa_id' => $waId,
                        'business_wa_id' => $businessWaId
                    ]);

                    if (!$waId) {
                        Log::warning('Mensaje sin WA ID válido', ['message' => $m]);
                        continue;
                    }

                    try {
                        $ts   = Carbon::createFromTimestamp($m['timestamp'], 'UTC');
                        $body = $m['text']['body'] ?? json_encode($m[$m['type']] ?? []);

                        // Upsert Contact
                        $contact = Contact::firstOrCreate(
                            ['wa_id' => $waId],
                            ['name'  => null]
                        );

                        // Actualizar el nombre del contacto si está disponible en el webhook
                        if (isset($change['value']['contacts'][0]['profile']['name'])) {
                            $contact->update([
                                'name' => $change['value']['contacts'][0]['profile']['name']
                            ]);
                        }

                        Log::info('Contacto procesado', [
                            'contact_id' => $contact->id,
                            'wa_id' => $contact->wa_id
                        ]);

                        // Upsert/open Conversation
                        $conversation = Conversation::firstOrCreate(
                            ['contact_id' => $contact->id, 'status' => 'open'],
                            ['last_message_at' => $ts]
                        );
                        Log::info('Conversación procesada', [
                            'conversation_id' => $conversation->id,
                            'contact_id' => $conversation->contact_id
                        ]);

                        // Matching por client_message_id primero
                        $message = Message::where('client_message_id', $m['id'])->first();
                        if ($message) {
                            // Actualiza el message_id oficial y el status, NO el timestamp ni el contenido
                            $message->update([
                                'message_id' => $m['id'],
                                'status' => 'sent'
                            ]);
                        } else {
                            // Si no existe por client_message_id, busca por message_id
                            $message = Message::where('message_id', $m['id'])->first();
                            if ($message) {
                                // Solo actualiza el status
                                $message->update([
                                    'status' => 'sent'
                                ]);
                            } else {
                                // Si no existe, crea el mensaje con el timestamp del webhook
                                Message::create([
                                    'message_id'      => $m['id'],
                                    'conversation_id' => $conversation->id,
                                    'from_me'         => $isOutgoing,
                                    'message_type'    => $m['type'],
                                    'content'         => $body,
                                    'timestamp'       => $ts,
                                    'status'          => 'sent'
                                ]);
                            }
                        }

                        $conversation->update(['last_message_at' => $ts]);
                    } catch (\Exception $e) {
                        Log::error('Error procesando mensaje', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'message_data' => $m
                        ]);
                    }
                }

                /*-- Procesa statuses --*/
                foreach ($change['value']['statuses'] ?? [] as $s) {
                    Log::info('Procesando status', [
                        'message_id' => $s['id'] ?? 'unknown',
                        'status' => $s['status'] ?? 'unknown',
                        'timestamp' => $s['timestamp'] ?? 'unknown'
                    ]);

                    try {
                        Message::where('message_id', $s['id'])
                            ->update(['status' => $s['status']]);
                        Log::info('Status actualizado', [
                            'message_id' => $s['id'],
                            'new_status' => $s['status']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error actualizando status', [
                            'error' => $e->getMessage(),
                            'status_data' => $s
                        ]);
                    }
                }
            }
        }

        Log::info('Webhook procesado exitosamente');
        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Webhook para enviar plantillas de WhatsApp desde sistemas externos
     */
    public function sendTemplateWebhook(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string',
            'template' => 'required|string',
            'language' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        $parameters = $validated['parameters'] ?? [];

        // Enviar plantilla usando el servicio
        $waService = app(\App\Services\WhatsAppService::class);
        $waResp = $waService->sendTemplate(
            $validated['to'],
            $validated['template'],
            $validated['language'],
            $parameters
        );

        // Registrar el mensaje en la base de datos si fue exitoso
        if ($waResp['success']) {
            // Buscar o crear contacto y conversación
            $contact = \App\Models\Chat\Contact::firstOrCreate([
                'wa_id' => $validated['to']
            ]);
            $conversation = \App\Models\Chat\Conversation::firstOrCreate([
                'contact_id' => $contact->id,
                'status' => 'open'
            ]);
            \App\Models\Chat\Message::create([
                'message_id' => $waResp['message_id'] ?? uniqid('template_'),
                'conversation_id' => $conversation->id,
                'from_me' => true,
                'message_type' => 'template',
                'content' => json_encode([
                    'template' => $validated['template'],
                    'parameters' => $parameters
                ]),
                'timestamp' => now(),
                'status' => 'sent'
            ]);
        }

        return response()->json($waResp, $waResp['success'] ? 200 : 500);
    }
}
