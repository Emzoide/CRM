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

    public function sendTemplate(string $to, string $template, string $language, array $payload = []): array
    {
        try {
            $token = $this->tokenService->getToken();
            if (!$token) {
                Log::error('No se pudo obtener un token válido para enviar la plantilla');
                return [
                    'success' => false,
                    'status' => 401,
                    'error' => 'Token inválido'
                ];
            }

            /* --- Construir componentes --- */
            $components = [];

            // BODY ──────────────────────────────────────────────
            $bodyParams = array_filter($payload, fn($p) => ($p['component'] ?? '') === 'body');
            if ($bodyParams) {
                $parameters = [];
                foreach ($bodyParams as $p) {
                    $param = [
                        'type' => 'text',
                        'text' => $p['text']
                    ];
                    // Requisito cuando parameter_format = NAMED
                    if (!empty($p['parameter_name'])) {
                        $param['parameter_name'] = $p['parameter_name'];
                    }
                    $parameters[] = $param;
                }

                $components[] = [
                    'type'       => 'body',   // *** minúsculas ***
                    'parameters' => $parameters
                ];
            }

            // BUTTONS ───────────────────────────────────────────
            $buttonParams = array_filter($payload, fn($p) => ($p['component'] ?? '') === 'button');
            foreach ($buttonParams as $p) {
                $components[] = [
                    'type'      => 'button',  // *** minúsculas ***
                    'sub_type'  => 'url',
                    'index'     => (int)($p['button_index'] ?? 0),
                    'parameters' => [[
                        'type' => 'text',
                        'text' => $p['text']
                    ]]
                ];
            }

            /* --- Payload final --- */
            $finalPayload = [
                'messaging_product' => 'whatsapp',
                'to'                => $to,
                'type'              => 'template',
                'template'          => [
                    'name'     => $template,
                    'language' => [
                        'code'   => $language,
                        'policy' => 'deterministic'
                    ],
                    'components' => $components
                ]
            ];

            /* --- Envío --- */
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $finalPayload);

            /* --- Reintento si expira el token --- */
            if ($response->status() === 401) {
                if ($token = $this->tokenService->renewToken()) {
                    $response = Http::withToken($token)
                        ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $finalPayload);
                } else {
                    Log::error('No se pudo renovar el token para enviar la plantilla');
                    return [
                        'success' => false,
                        'status' => 401,
                        'error' => 'No se pudo renovar el token'
                    ];
                }
            }

            /* --- Respuesta OK --- */
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

            /* --- Error de la API --- */
            $error = $response->json();
            Log::error('Error al enviar plantilla de WhatsApp', [
                'status'    => $response->status(),
                'response'  => $error
            ]);
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $error['error']['message'] ??
                    'Error desconocido al enviar plantilla'
            ];
        } catch (\Throwable $e) {
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
