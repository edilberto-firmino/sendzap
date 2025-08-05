<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CampaignLog;
use Exception;

class WhatsAppService
{
    private string $baseUrl;
    private int $timeout;
    private int $messageDelay;
    private int $maxRetries;
    private int $dailyLimit;
    private int $hourlyLimit;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.base_url', 'http://localhost:3001');
        $this->timeout = config('services.whatsapp.timeout', 30);
        $this->messageDelay = config('services.whatsapp.message_delay', 2);
        $this->maxRetries = config('services.whatsapp.max_retries', 3);
        $this->dailyLimit = config('services.whatsapp.daily_limit', 1000);
        $this->hourlyLimit = config('services.whatsapp.hourly_limit', 100);
    }

    /**
     * Verificar status da conexão WhatsApp
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/status');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error',
                'isConnected' => false,
                'error' => 'Erro ao conectar com o serviço WhatsApp'
            ];
        } catch (Exception $e) {
            Log::error('Erro ao verificar status WhatsApp: ' . $e->getMessage());
            return [
                'status' => 'error',
                'isConnected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter QR Code para conexão
     */
    public function getQrCode(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/qr');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'qr' => null,
                'status' => 'error',
                'message' => 'Erro ao obter QR Code'
            ];
        } catch (Exception $e) {
            Log::error('Erro ao obter QR Code: ' . $e->getMessage());
            return [
                'qr' => null,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar mensagem individual
     */
    public function sendMessage(string $phone, string $message, ?int $campaignLogId = null, string $messageType = 'text'): array
    {
        try {
            $payload = [
                'phone' => $phone,
                'message' => $message,
                'messageType' => $messageType
            ];

            if ($campaignLogId) {
                $payload['campaignId'] = $campaignLogId;
            }

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send-message', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Atualizar log se fornecido
                if ($campaignLogId && $data['success']) {
                    $this->updateCampaignLog($campaignLogId, $data);
                }
                
                return $data;
            }

            $errorData = [
                'success' => false,
                'error' => 'Erro ao enviar mensagem',
                'status' => 'failed'
            ];

            // Atualizar log com erro
            if ($campaignLogId) {
                $this->updateCampaignLog($campaignLogId, $errorData);
            }

            return $errorData;

        } catch (Exception $e) {
            Log::error('Erro ao enviar mensagem WhatsApp: ' . $e->getMessage(), [
                'phone' => $phone,
                'campaignLogId' => $campaignLogId
            ]);

            $errorData = [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];

            // Atualizar log com erro
            if ($campaignLogId) {
                $this->updateCampaignLog($campaignLogId, $errorData);
            }

            return $errorData;
        }
    }

    /**
     * Enviar campanha completa
     */
    public function sendCampaign(array $messages): array
    {
        try {
            $response = Http::timeout($this->timeout * 2) // Timeout maior para campanhas
                ->post($this->baseUrl . '/send-campaign', [
                    'messages' => $messages
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Erro ao enviar campanha',
                'results' => []
            ];

        } catch (Exception $e) {
            Log::error('Erro ao enviar campanha WhatsApp: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => []
            ];
        }
    }

    /**
     * Desconectar WhatsApp
     */
    public function disconnect(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/disconnect');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Erro ao desconectar WhatsApp'
            ];

        } catch (Exception $e) {
            Log::error('Erro ao desconectar WhatsApp: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar se o serviço está online
     */
    public function isOnline(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get($this->baseUrl . '/health');

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verificar se WhatsApp está conectado
     */
    public function isConnected(): bool
    {
        $status = $this->getStatus();
        return $status['isConnected'] ?? false;
    }

    /**
     * Limpar dados de autenticação e forçar nova conexão
     */
    public function clearAuth(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/clear-auth');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Erro ao limpar dados de autenticação'
            ];

        } catch (Exception $e) {
            Log::error('Erro ao limpar dados de autenticação: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Atualizar log da campanha
     */
    private function updateCampaignLog(int $campaignLogId, array $data): void
    {
        try {
            $log = CampaignLog::find($campaignLogId);
            
            if (!$log) {
                Log::warning("CampaignLog não encontrado: {$campaignLogId}");
                return;
            }

            if ($data['success']) {
                $log->markAsSent($data['messageId'] ?? null);
            } else {
                $log->markAsFailed($data['error'] ?? 'Erro desconhecido');
            }

        } catch (Exception $e) {
            Log::error('Erro ao atualizar CampaignLog: ' . $e->getMessage(), [
                'campaignLogId' => $campaignLogId,
                'data' => $data
            ]);
        }
    }

    /**
     * Formatar telefone para o padrão WhatsApp
     */
    public function formatPhone(string $phone): string
    {
        // Remove todos os caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);
        
        // Adiciona código do Brasil se não tiver
        if (!str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }

    /**
     * Validar formato do telefone
     */
    public function validatePhone(string $phone): bool
    {
        $formatted = $this->formatPhone($phone);
        return strlen($formatted) >= 12 && strlen($formatted) <= 13;
    }
} 