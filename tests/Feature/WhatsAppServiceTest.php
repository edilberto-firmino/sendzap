<?php

namespace Tests\Feature;

use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_check_service_status()
    {
        Http::fake([
            'localhost:3001/status' => Http::response([
                'status' => 'connected',
                'isConnected' => true,
                'timestamp' => now()->toISOString()
            ], 200)
        ]);

        $service = new WhatsAppService();
        $status = $service->getStatus();

        $this->assertTrue($status['isConnected']);
        $this->assertEquals('connected', $status['status']);
    }

    #[Test]
    public function it_returns_error_when_service_is_offline()
    {
        Http::fake([
            'localhost:3001/status' => Http::response([], 500)
        ]);

        $service = new WhatsAppService();
        $status = $service->getStatus();

        $this->assertFalse($status['isConnected']);
        $this->assertEquals('error', $status['status']);
    }

    #[Test]
    public function it_can_get_qr_code()
    {
        Http::fake([
            'localhost:3001/qr' => Http::response([
                'qr' => 'test-qr-code',
                'status' => 'qr_ready'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $result = $service->getQrCode();

        $this->assertEquals('test-qr-code', $result['qr']);
        $this->assertEquals('qr_ready', $result['status']);
    }

    #[Test]
    public function it_can_send_message()
    {
        Http::fake([
            'localhost:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'test-id',
                'status' => 'sent'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $result = $service->sendMessage('+5511999999999', 'Teste');

        $this->assertTrue($result['success']);
        $this->assertEquals('sent', $result['status']);
    }

    #[Test]
    public function it_handles_message_send_failure()
    {
        Http::fake([
            'localhost:3001/send-message' => Http::response([
                'success' => false,
                'error' => 'Erro de envio',
                'status' => 'failed'
            ], 400)
        ]);

        $service = new WhatsAppService();
        $result = $service->sendMessage('+5511999999999', 'Teste');

        $this->assertFalse($result['success']);
        $this->assertEquals('failed', $result['status']);
    }

    #[Test]
    public function it_can_format_phone_numbers()
    {
        $service = new WhatsAppService();

        $this->assertEquals('5511999999999', $service->formatPhone('11999999999'));
        $this->assertEquals('5511999999999', $service->formatPhone('+5511999999999'));
        $this->assertEquals('5511999999999', $service->formatPhone('(11) 99999-9999'));
    }

    #[Test]
    public function it_can_validate_phone_numbers()
    {
        $service = new WhatsAppService();

        $this->assertTrue($service->validatePhone('11999999999'));
        $this->assertTrue($service->validatePhone('+5511999999999'));
        $this->assertFalse($service->validatePhone('123'));
        $this->assertFalse($service->validatePhone(''));
    }

    #[Test]
    public function it_can_check_if_service_is_online()
    {
        Http::fake([
            'localhost:3001/health' => Http::response([], 200)
        ]);

        $service = new WhatsAppService();
        $this->assertTrue($service->isOnline());
    }

    #[Test]
    public function it_returns_false_when_service_is_offline()
    {
        Http::fake([
            'localhost:3001/health' => Http::response([], 500)
        ]);

        $service = new WhatsAppService();
        $this->assertFalse($service->isOnline());
    }

    #[Test]
    public function it_can_disconnect_whatsapp()
    {
        Http::fake([
            'localhost:3001/disconnect' => Http::response([
                'success' => true,
                'message' => 'Desconectado com sucesso'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $result = $service->disconnect();

        $this->assertTrue($result['success']);
    }
} 