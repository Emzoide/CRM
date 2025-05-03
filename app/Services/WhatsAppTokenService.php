<?php

namespace App\Services;

use App\Models\Chat\WhatsAppToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class WhatsAppTokenService
{
    private string $baseUrl;
    private string $appId;
    private string $appSecret;
    private string $phoneNumberId;

    public function __construct()
    {
        try {
            $config = Config::get('whatsapp');

            if (!$config) {
                throw new \RuntimeException('La configuración de WhatsApp no está disponible.');
            }

            $this->baseUrl = $config['base_url'] . '/' . $config['api_version'];
            $this->appId = $config['app_id'];
            $this->appSecret = $config['app_secret'];
            $this->phoneNumberId = $config['phone_number_id'];

            if (empty($this->appId) || empty($this->appSecret) || empty($this->phoneNumberId)) {
                throw new \RuntimeException('Las credenciales de WhatsApp no están configuradas correctamente. Por favor, verifica las variables de entorno.');
            }
        } catch (\Exception $e) {
            Log::error('Error al inicializar WhatsAppTokenService: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getToken(): ?string
    {
        $token = WhatsAppToken::getActiveToken();
        if (!$token) {
            return $this->renewToken();
        }
        return $token->token;
    }

    public function renewToken(): ?string
    {
        Log::info('Renovando token de WhatsApp');

        try {
            $response = Http::get("{$this->baseUrl}/oauth/access_token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? (Config::get('whatsapp.token_expiry_days') * 24 * 60 * 60);

                // Desactivar todos los tokens existentes
                WhatsAppToken::deactivateAllTokens();

                // Crear nuevo token
                WhatsAppToken::create([
                    'token' => $token,
                    'expires_at' => Carbon::now()->addSeconds($expiresIn),
                    'is_active' => true
                ]);

                Log::info('Token de WhatsApp renovado exitosamente');
                return $token;
            }

            Log::error('Error al renovar token de WhatsApp', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Excepción al renovar token de WhatsApp', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
