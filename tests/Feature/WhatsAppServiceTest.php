<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WhatsAppService;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\CampaignLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    private WhatsAppService $whatsappService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->whatsappService = new WhatsAppService();
    }

    /** @test */
    public function it_can_check_service_status()
    {
        Http::fake([
            'localhost:3001/status' => Http::response([
                'status' => 'connected',
                'isConnected' => true,
                'timestamp' => now()->toISOString()
            ], 200)
        ]);

        $status = $this->whatsappService->getStatus();

        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('isConnected', $status);
        $this->assertTrue($status['isConnected']);
    }

    /** @test */
    public function it_returns_error_when_service_is_offline()
    {
        Http::fake([
            'localhost:3001/status' => Http::response([], 500)
        ]);

        $status = $this->whatsappService->getStatus();

        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('isConnected', $status);
        $this->assertFalse($status['isConnected']);
        $this->assertEquals('error', $status['status']);
    }

    /** @test */
    public function it_can_get_qr_code()
    {
        Http::fake([
            'localhost:3001/qr' => Http::response([
                'qr' => 'test-qr-code',
                'status' => 'qr_ready'
            ], 200)
        ]);

        $qrData = $this->whatsappService->getQrCode();

        $this->assertArrayHasKey('qr', $qrData);
        $this->assertArrayHasKey('status', $qrData);
        $this->assertEquals('test-qr-code', $qrData['qr']);
        $this->assertEquals('qr_ready', $qrData['status']);
    }

    /** @test */
    public function it_can_send_message()
    {
        Http::fake([
            'localhost:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'test-message-id',
                'status' => 'sent'
            ], 200)
        ]);

        $result = $this->whatsappService->sendMessage('+5511999999999', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertEquals('test-message-id', $result['messageId']);
        $this->assertEquals('sent', $result['status']);
    }

    /** @test */
    public function it_handles_message_send_failure()
    {
        Http::fake([
            'localhost:3001/send-message' => Http::response([
                'success' => false,
                'error' => 'WhatsApp not connected',
                'status' => 'failed'
            ], 400)
        ]);

        $result = $this->whatsappService->sendMessage('+5511999999999', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertEquals('failed', $result['status']);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function it_can_format_phone_numbers()
    {
        $formatted = $this->whatsappService->formatPhone('11999999999');
        $this->assertEquals('5511999999999', $formatted);

        $formatted = $this->whatsappService->formatPhone('+5511999999999');
        $this->assertEquals('5511999999999', $formatted);

        $formatted = $this->whatsappService->formatPhone('(11) 99999-9999');
        $this->assertEquals('5511999999999', $formatted);
    }

    /** @test */
    public function it_can_validate_phone_numbers()
    {
        $this->assertTrue($this->whatsappService->validatePhone('11999999999'));
        $this->assertTrue($this->whatsappService->validatePhone('+5511999999999'));
        $this->assertFalse($this->whatsappService->validatePhone('9999999')); // Muito curto
        $this->assertFalse($this->whatsappService->validatePhone('551199999999999999')); // Muito longo
    }

    /** @test */
    public function it_can_check_if_service_is_online()
    {
        Http::fake([
            'localhost:3001/health' => Http::response(['status' => 'ok'], 200)
        ]);

        $this->assertTrue($this->whatsappService->isOnline());
    }

    /** @test */
    public function it_returns_false_when_service_is_offline()
    {
        Http::fake([
            'localhost:3001/health' => Http::response([], 500)
        ]);

        $this->assertFalse($this->whatsappService->isOnline());
    }

    /** @test */
    public function it_can_disconnect_whatsapp()
    {
        Http::fake([
            'localhost:3001/disconnect' => Http::response([
                'success' => true,
                'message' => 'WhatsApp desconectado com sucesso'
            ], 200)
        ]);

        $result = $this->whatsappService->disconnect();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
} 