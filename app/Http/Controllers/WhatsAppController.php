<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Models\Chat\{Contact, Conversation, Message};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Normaliza un número de teléfono para búsqueda consistente
     */
    private function normalizePhoneNumber($number)
    {
        // Eliminar todos los caracteres no numéricos
        $number = preg_replace('/[^0-9]/', '', $number);

        // Si el número comienza con 51, lo dejamos así
        if (substr($number, 0, 2) === '51') {
            return $number;
        }

        // Si el número comienza con 5, agregamos el 1
        if (substr($number, 0, 1) === '5') {
            return '51' . $number;
        }

        // Si el número comienza con 9, agregamos el 51
        if (substr($number, 0, 1) === '9') {
            return '51' . $number;
        }

        return $number;
    }

    public function sendContactTemplate(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'numero' => 'required|string',
                'contacto_nombre' => 'required|string',
                'marca' => 'required|string',
                'modelo' => 'required|string',
                'chatbot_id' => 'required|string',
            ]);

            // Extraer el primer número de la cadena si hay múltiples
            $numero = explode(',', $request->numero)[0];
            $numero = trim($numero); // Eliminar espacios en blanco

            // Normalizar el número de teléfono
            $normalizedNumber = $this->normalizePhoneNumber($numero);

            // Asegurarnos de que el número tenga el formato correcto para WhatsApp
            if (!str_starts_with($normalizedNumber, '51')) {
                $normalizedNumber = '51' . $normalizedNumber;
            }

            // Obtener la ficha técnica
            $dataSheetResponse = Http::get("https://interamericana-norte.com/api/data-sheet/{$request->modelo}");

            if (!$dataSheetResponse->successful()) {
                Log::error('Error al obtener la ficha técnica', [
                    'modelo' => $request->modelo,
                    'response' => $dataSheetResponse->json()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo obtener la ficha técnica del modelo'
                ], 400);
            }

            $dataSheet = $dataSheetResponse->json();

            if (!$dataSheet['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se encontró la ficha técnica para este modelo'
                ], 404);
            }

            // Preparar los parámetros para el template
            $payload = [
                // Header parameter
                [
                    'type' => 'text',
                    'parameter_name' => 'header',
                    'text' => 'Más que un vehículo, tu próxima aventura.',
                    'component' => 'header'
                ],
                // Body parameters
                [
                    'type' => 'text',
                    'parameter_name' => 'contacto_nombre',
                    'text' => $request->contacto_nombre,
                    'component' => 'body'
                ],
                [
                    'type' => 'text',
                    'parameter_name' => 'marca_modelo',
                    'text' => "{$request->marca} {$request->modelo}",
                    'component' => 'body'
                ],
                // Button parameters
                [
                    'type' => 'text',
                    'parameter_name' => 'button_0_1',
                    'text' => $request->chatbot_id,
                    'component' => 'button',
                    'button_index' => '0',
                    'param_number' => '1'
                ],
                [
                    'type' => 'text',
                    'parameter_name' => 'button_1_1',
                    'text' => $dataSheet['filename'],
                    'component' => 'button',
                    'button_index' => '1',
                    'param_number' => '1'
                ]
            ];

            // Enviar el template
            $result = $this->whatsAppService->sendTemplate(
                $normalizedNumber,
                'contacto_primero',
                'es_PE',
                $payload
            );

            if (!$result['success']) {
                Log::error('Error al enviar el template de WhatsApp', [
                    'numero' => $normalizedNumber,
                    'error' => $result['error']
                ]);
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], $result['status']);
            }

            // Buscar contacto existente por número normalizado
            $contact = Contact::where('wa_id', 'LIKE', '%' . $normalizedNumber . '%')
                ->orWhere('wa_id', 'LIKE', '%' . substr($normalizedNumber, -9) . '%')
                ->first();

            if (!$contact) {
                // Si no existe, crear nuevo contacto
                $contact = Contact::create([
                    'wa_id' => $normalizedNumber,
                    'name' => $request->contacto_nombre,
                    'metadata' => [
                        'marca' => $request->marca,
                        'modelo' => $request->modelo,
                        'chatbot_id' => $request->chatbot_id
                    ]
                ]);
            } else {
                // Actualizar nombre y metadata si es necesario
                $contact->update([
                    'name' => $request->contacto_nombre,
                    'metadata' => array_merge($contact->metadata ?? [], [
                        'marca' => $request->marca,
                        'modelo' => $request->modelo,
                        'chatbot_id' => $request->chatbot_id
                    ])
                ]);
            }

            // Buscar conversación existente
            $conversation = Conversation::where('contact_id', $contact->id)
                ->where('status', 'open')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'contact_id' => $contact->id,
                    'status' => 'open',
                    'last_message_at' => now()
                ]);
            }

            // Crear el mensaje
            $message = Message::create([
                'message_id' => $result['message_id'] ?? uniqid('template_'),
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
                'contact_id' => $contact->id,
                'numero_original' => $numero,
                'numero_normalizado' => $normalizedNumber,
                'chatbot_id' => $request->chatbot_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente',
                'message_id' => $result['message_id'],
                'data' => [
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'contact_id' => $contact->id,
                    'chatbot_id' => $request->chatbot_id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en el proceso de envío', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Envía una plantilla de reactivación a un contacto
     */
    public function sendReactivationTemplate(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'numero' => 'required|string',
                'contacto_nombre' => 'required|string',
                'chatbot_id' => 'required|string',
            ]);

            // Extraer el primer número de la cadena si hay múltiples
            $numero = explode(',', $request->numero)[0];
            $numero = trim($numero); // Eliminar espacios en blanco

            // Normalizar el número de teléfono
            $normalizedNumber = $this->normalizePhoneNumber($numero);

            // Asegurarnos de que el número tenga el formato correcto para WhatsApp
            if (!str_starts_with($normalizedNumber, '51')) {
                $normalizedNumber = '51' . $normalizedNumber;
            }

            // Preparar los parámetros para el template
            $payload = [
                // Header parameter
                [
                    'type' => 'text',
                    'parameter_name' => 'header',
                    'text' => 'Tu próximo paso nos importa.',
                    'component' => 'header'
                ],
                // Body parameters
                [
                    'type' => 'text',
                    'parameter_name' => 'contacto_nombre',
                    'text' => $request->contacto_nombre,
                    'component' => 'body'
                ],
                // Button parameters
                [
                    'type' => 'text',
                    'parameter_name' => 'button_0_1',
                    'text' => $request->chatbot_id,
                    'component' => 'button',
                    'button_index' => '0',
                    'param_number' => '1'
                ]
            ];

            // Enviar el template
            $result = $this->whatsAppService->sendTemplate(
                $normalizedNumber,
                'reactivacion',
                'es_PE',
                $payload
            );

            if (!$result['success']) {
                Log::error('Error al enviar el template de reactivación', [
                    'numero' => $normalizedNumber,
                    'error' => $result['error']
                ]);
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], $result['status']);
            }

            // Buscar contacto existente por número normalizado
            $contact = Contact::where('wa_id', 'LIKE', '%' . $normalizedNumber . '%')
                ->orWhere('wa_id', 'LIKE', '%' . substr($normalizedNumber, -9) . '%')
                ->first();

            if (!$contact) {
                // Si no existe, crear nuevo contacto
                $contact = Contact::create([
                    'wa_id' => $normalizedNumber,
                    'name' => $request->contacto_nombre,
                    'metadata' => [
                        'chatbot_id' => $request->chatbot_id
                    ]
                ]);
            } else {
                // Actualizar nombre y metadata si es necesario
                $contact->update([
                    'name' => $request->contacto_nombre,
                    'metadata' => array_merge($contact->metadata ?? [], [
                        'chatbot_id' => $request->chatbot_id
                    ])
                ]);
            }

            // Buscar conversación existente
            $conversation = Conversation::where('contact_id', $contact->id)
                ->where('status', 'open')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'contact_id' => $contact->id,
                    'status' => 'open',
                    'last_message_at' => now()
                ]);
            }

            // Crear el mensaje
            $message = Message::create([
                'message_id' => $result['message_id'] ?? uniqid('template_'),
                'conversation_id' => $conversation->id,
                'from_me' => true,
                'message_type' => 'template',
                'content' => json_encode($payload),
                'timestamp' => now(),
                'status' => 'sent'
            ]);

            // Actualizar timestamp de última conversación
            $conversation->update(['last_message_at' => now()]);

            Log::info('Plantilla de reactivación enviada y registrada exitosamente', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'contact_id' => $contact->id,
                'numero_original' => $numero,
                'numero_normalizado' => $normalizedNumber,
                'chatbot_id' => $request->chatbot_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje de reactivación enviado exitosamente',
                'message_id' => $result['message_id'],
                'data' => [
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'contact_id' => $contact->id,
                    'chatbot_id' => $request->chatbot_id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en el proceso de envío de reactivación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}
