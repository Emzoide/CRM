<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class WhatsAppService
{
    private string $baseUrl;
    private string $phoneNumberId;
    private WhatsAppTokenService $tokenService;

    public function __construct(WhatsAppTokenService $tokenService)
    {
        $this->baseUrl = Config::get('whatsapp.base_url') . '/' . Config::get('whatsapp.api_version');
        $this->phoneNumberId = Config::get('whatsapp.phone_number_id');
        $this->tokenService = $tokenService;
    }

    public function sendMessage(string $to, string $message): array
    {
        try {
            $token = $this->tokenService->getToken();
            if (!$token) {
                Log::error('No se pudo obtener un token válido para enviar el mensaje');
                return [
                    'success' => false,
                    'status' => 401,
                    'error' => 'No se pudo obtener un token válido para enviar el mensaje'
                ];
            }

            Log::info('Preparando envío a WhatsApp', [
                'endpoint' => "{$this->baseUrl}/{$this->phoneNumberId}/messages",
                'token' => substr($token, 0, 10) . '...' . substr($token, -10),
                'to' => $to,
                'payload' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $message]
                ]
            ]);

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $message]
                ]);

            if ($response->status() === 401) {
                // Token expirado, intentar renovar
                $token = $this->tokenService->renewToken();
                if (!$token) {
                    Log::error('No se pudo renovar el token para enviar el mensaje');
                    return [
                        'success' => false,
                        'status' => 401,
                        'error' => 'No se pudo renovar el token para enviar el mensaje'
                    ];
                }

                // Reintentar con el nuevo token
                $response = Http::withToken($token)
                    ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to' => $to,
                        'type' => 'text',
                        'text' => ['body' => $message]
                    ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Mensaje enviado exitosamente', [
                    'message_id' => $data['messages'][0]['id'] ?? null
                ]);
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message_id' => $data['messages'][0]['id'] ?? null
                ];
            }

            $errorJson = $response->json();
            Log::error('Error al enviar mensaje de WhatsApp', [
                'status' => $response->status(),
                'response' => $errorJson
            ]);
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $errorJson['error']['message'] ?? 'Error desconocido al enviar mensaje a WhatsApp'
            ];
        } catch (\Exception $e) {
            Log::error('Excepción al enviar mensaje de WhatsApp', [
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

    public function sendTemplate(string $to, string $template, string $language, array $parameters = []): array
    {
        try {
            $token = $this->tokenService->getToken();
            if (!$token) {
                Log::error('No se pudo obtener un token válido para enviar la plantilla');
                return [
                    'success' => false,
                    'status' => 401,
                    'error' => 'No se pudo obtener un token válido para enviar la plantilla'
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => $language],
                ]
            ];

            if (!empty($parameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => $parameters
                    ]
                ];
            }

            Log::info('Enviando plantilla WhatsApp', [
                'endpoint' => "{$this->baseUrl}/{$this->phoneNumberId}/messages",
                'to' => $to,
                'payload' => $payload
            ]);

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->status() === 401) {
                $token = $this->tokenService->renewToken();
                if (!$token) {
                    Log::error('No se pudo renovar el token para enviar la plantilla');
                    return [
                        'success' => false,
                        'status' => 401,
                        'error' => 'No se pudo renovar el token para enviar la plantilla'
                    ];
                }
                $response = Http::withToken($token)
                    ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);
            }

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Plantilla enviada exitosamente', [
                    'message_id' => $data['messages'][0]['id'] ?? null
                ]);
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message_id' => $data['messages'][0]['id'] ?? null
                ];
            }

            $errorJson = $response->json();
            Log::error('Error al enviar plantilla de WhatsApp', [
                'status' => $response->status(),
                'response' => $errorJson
            ]);
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $errorJson['error']['message'] ?? 'Error desconocido al enviar plantilla a WhatsApp'
            ];
        } catch (\Exception $e) {
            Log::error('Excepción al enviar plantilla de WhatsApp', [
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }
}
