<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    private WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Exibe página de conexão WhatsApp
     */
    public function connectForm()
    {
        $status = $this->whatsappService->getStatus();
        $isOnline = $this->whatsappService->isOnline();

        return view('whatsapp.connect', compact('status', 'isOnline'));
    }

    /**
     * Obter QR Code via AJAX
     */
    public function getQrCode()
    {
        $qrData = $this->whatsappService->getQrCode();

        return response()->json($qrData);
    }

    /**
     * Obter QR Code como imagem
     */
    public function getQrCodeImage()
    {
        $qrData = $this->whatsappService->getQrCode();
        
        if (!$qrData['qr']) {
            return response()->json(['error' => 'QR Code não disponível'], 404);
        }

        try {
            // Usar a biblioteca Endroid QR Code para gerar QR code válido
            $qrCode = new \Endroid\QrCode\QrCode($qrData['qr']);

            // Gerar como PNG
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            // Output como PNG
            header('Content-Type: image/png');
            echo $result->getString();
            exit;
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar QR Code: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao gerar QR Code'], 500);
        }
    }

    /**
     * Obter status da conexão
     */
    public function getStatus()
    {
        $status = $this->whatsappService->getStatus();

        return response()->json($status);
    }

    /**
     * Desconectar WhatsApp
     */
    public function disconnect()
    {
        try {
            $result = $this->whatsappService->disconnect();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp desconectado com sucesso!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao desconectar: ' . $result['error']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao desconectar WhatsApp'
            ], 500);
        }
    }

    /**
     * Enviar mensagem de teste
     */
    public function sendTestMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:1000'
        ]);

        try {
            $result = $this->whatsappService->sendMessage(
                $request->phone,
                $request->message
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso!',
                    'messageId' => $result['messageId'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar mensagem: ' . ($result['error'] ?? 'Erro desconhecido')
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem de teste: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao enviar mensagem'
            ], 500);
        }
    }

    /**
     * Verificar se o serviço está online
     */
    public function healthCheck()
    {
        $isOnline = $this->whatsappService->isOnline();
        $isConnected = $this->whatsappService->isConnected();

        return response()->json([
            'service_online' => $isOnline,
            'whatsapp_connected' => $isConnected,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Limpar dados de autenticação e forçar nova conexão
     */
    public function clearAuth()
    {
        try {
            $result = $this->whatsappService->clearAuth();

            if ($result['success']) {
                return redirect()->route('whatsapp.connect')
                    ->with('success', 'Dados de autenticação limpos! Nova conexão será iniciada automaticamente.');
            } else {
                return redirect()->route('whatsapp.connect')
                    ->with('error', 'Erro ao limpar dados: ' . $result['error']);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao limpar dados de autenticação: ' . $e->getMessage());
            
            return redirect()->route('whatsapp.connect')
                ->with('error', 'Erro interno ao limpar dados de autenticação');
        }
    }
} 