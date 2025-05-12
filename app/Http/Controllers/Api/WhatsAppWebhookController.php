<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use App\Models\Chat\{Contact, Conversation, Message, WebhookEvent};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WhatsAppWebhookController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Endpoint para recibir webhooks del CRM y enviar plantillas de WhatsApp
     */
    public function sendTemplate(Request $request)
    {
        try {
            // Validar los campos requeridos
            $validator = Validator::make($request->all(), [
                'to' => 'required|string',
                'template_name' => 'required|string',
                'language' => 'required|string',
                'parameters' => 'nullable|array',
                'parameters.*.type' => 'required_with:parameters|string|in:text,currency,date_time,image,document',
                'parameters.*.parameter_name' => 'required_with:parameters|string',
                'parameters.*.text' => 'required_if:parameters.*.type,text|string',
                'parameters.*.component' => 'required_with:parameters|string|in:body,button',
                'parameters.*.button_index' => 'required_if:parameters.*.component,button',
                'contact_name' => 'nullable|string',
                'contact_metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error de validación',
                    'details' => $validator->errors()
                ], 422);
            }

            // Registrar el evento del webhook
            $webhookEvent = WebhookEvent::create([
                'event_type' => 'template_webhook',
                'payload' => $request->all(),
                'received_at' => now(),
            ]);

            Log::info('Webhook de plantilla recibido', [
                'event_id' => $webhookEvent->id,
                'to' => $request->to,
                'template' => $request->template_name
            ]);

            // Preparar los componentes para WhatsApp
            $components = [];
            // BODY
            $bodyParams = array_filter($request->parameters ?? [], function ($p) {
                return (isset($p['component']) && $p['component'] === 'body');
            });
            if (count($bodyParams)) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => array_map(function ($p) {
                        return [
                            'type' => $p['type'],
                            'text' => $p['text']
                        ];
                    }, $bodyParams)
                ];
            }
            // BUTTONS
            $buttonParams = array_filter($request->parameters ?? [], function ($p) {
                return (isset($p['component']) && $p['component'] === 'button');
            });
            if (count($buttonParams)) {
                // Agrupar por button_index
                $grouped = [];
                foreach ($buttonParams as $p) {
                    $idx = (string)($p['button_index'] ?? '0');
                    $grouped[$idx][] = [
                        'type' => $p['type'],
                        'text' => $p['text']
                    ];
                }
                foreach ($grouped as $idx => $params) {
                    $components[] = [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => (int)$idx,
                        'parameters' => $params
                    ];
                }
            }
            $payload = [
                'template' => [
                    'components' => $components
                ]
            ];

            // Enviar la plantilla
            $response = $this->whatsAppService->sendTemplate(
                $request->to,
                $request->template_name,
                $request->language,
                $payload
            );

            if ($response['success']) {
                // Buscar o crear contacto
                $contact = Contact::firstOrCreate(
                    ['wa_id' => $request->to],
                    [
                        'name' => $request->contact_name,
                        'metadata' => $request->contact_metadata
                    ]
                );

                // Buscar o crear conversación
                $conversation = Conversation::firstOrCreate(
                    ['contact_id' => $contact->id, 'status' => 'open'],
                    ['last_message_at' => now()]
                );

                // Crear el mensaje
                $message = Message::create([
                    'message_id' => $response['message_id'] ?? uniqid('template_'),
                    'conversation_id' => $conversation->id,
                    'from_me' => true,
                    'message_type' => 'template',
                    'content' => json_encode($payload),
                    'timestamp' => now(),
                    'status' => 'sent'
                ]);

                // Actualizar timestamp de última conversación
                $conversation->update(['last_message_at' => now()]);

                Log::info('Plantilla enviada y registrada exitosamente', [
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'contact_id' => $contact->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Plantilla enviada exitosamente',
                    'data' => [
                        'message_id' => $message->id,
                        'conversation_id' => $conversation->id,
                        'contact_id' => $contact->id
                    ]
                ]);
            }

            Log::error('Error al enviar plantilla', [
                'error' => $response['error'] ?? 'Error desconocido',
                'status' => $response['status'] ?? 500
            ]);

            return response()->json($response, $response['status'] ?? 500);
        } catch (\Exception $e) {
            Log::error('Error en webhook de WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
