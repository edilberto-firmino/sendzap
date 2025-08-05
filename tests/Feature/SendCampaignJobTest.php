<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\CampaignLog;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use Mockery;

class SendCampaignJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_dispatch_campaign_job()
    {
        Queue::fake();

        $campaign = Campaign::factory()->create([
            'status' => 'draft',
            'message_text' => 'Olá {nome}, teste de campanha!'
        ]);

        $contact = Contact::factory()->create([
            'name' => 'João Silva',
            'phone' => '+5511999999999'
        ]);

        SendCampaignJob::dispatch($campaign);

        Queue::assertPushed(SendCampaignJob::class);
    }

    /** @test */
    public function it_creates_campaign_logs_for_contacts()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft',
            'message_text' => 'Teste de campanha'
        ]);

        $contacts = collect();
        for ($i = 1; $i <= 3; $i++) {
            $contacts->push(Contact::factory()->create([
                'phone' => '+551199999999' . $i
            ]));
        }

        $job = new SendCampaignJob($campaign);
        
        // Mock do HTTP client para simular sucesso e conexão
        Http::fake([
            'localhost:3001/status' => Http::response([
                'status' => 'connected',
                'isConnected' => true,
                'timestamp' => now()->toISOString()
            ], 200),
            'localhost:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'test-id',
                'status' => 'sent'
            ], 200)
        ]);

        $whatsappService = new WhatsAppService();

        $job->handle($whatsappService);

        // Verificar se os logs foram criados e atualizados
        $this->assertEquals(3, CampaignLog::where('campaign_id', $campaign->id)->count());
        
        foreach ($contacts as $contact) {
            $log = CampaignLog::where('campaign_id', $campaign->id)
                ->where('contact_id', $contact->id)
                ->first();
            
            $this->assertNotNull($log);
            $this->assertEquals('sent', $log->status);
        }
    }

    /** @test */
    public function it_handles_whatsapp_not_connected()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        $job = new SendCampaignJob($campaign);
        
        // Mock do WhatsAppService retornando não conectado
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('WhatsApp não está conectado');

        $job->handle($whatsappService);

        // Verificar se a campanha foi marcada como pausada
        $this->assertEquals('paused', $campaign->fresh()->status);
    }

    /** @test */
    public function it_handles_empty_contacts_list()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        $job = new SendCampaignJob($campaign);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);

        $job->handle($whatsappService);

        // Verificar se não houve erro e a campanha permanece como draft
        $this->assertEquals('draft', $campaign->fresh()->status);
    }

    /** @test */
    public function it_replaces_variables_in_message()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft',
            'message_text' => 'Olá {nome}, você mora em {cidade}?'
        ]);

        $contact = Contact::factory()->create([
            'name' => 'Maria Santos',
            'city' => 'São Paulo',
            'phone' => '+5511999999999'
        ]);

        $job = new SendCampaignJob($campaign);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);
        $whatsappService->shouldReceive('sendMessage')
            ->with('+5511999999999', 'Olá Maria Santos, você mora em São Paulo?', Mockery::any())
            ->andReturn(['success' => true]);

        $job->handle($whatsappService);
    }

    /** @test */
    public function it_handles_message_send_failure()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft',
            'message_text' => 'Teste'
        ]);

        $contact = Contact::factory()->create([
            'phone' => '+5511999999999'
        ]);

        // Criar log manualmente
        $log = CampaignLog::create([
            'campaign_id' => $campaign->id,
            'contact_id' => $contact->id,
            'status' => 'pending'
        ]);

        // Testar diretamente o WhatsAppService
        $whatsappService = new WhatsAppService();
        
        // Mock do HTTP client
        Http::fake([
            'localhost:3001/send-message' => Http::response([
                'success' => false,
                'error' => 'Erro de envio',
                'status' => 'failed'
            ], 400)
        ]);

        $result = $whatsappService->sendMessage('+5511999999999', 'Teste', $log->id);

        $this->assertFalse($result['success']);
        
        // Verificar se o log foi atualizado
        $log->refresh();
        $this->assertEquals('failed', $log->status);
        $this->assertEquals('Erro ao enviar mensagem', $log->error_message);
    }

    /** @test */
    public function it_updates_campaign_counters()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft',
            'total_contacts' => 0,
            'sent_count' => 0,
            'failed_count' => 0
        ]);

        $contacts = collect();
        for ($i = 10; $i <= 11; $i++) {
            $contacts->push(Contact::factory()->create([
                'phone' => '+551199999999' . $i
            ]));
        }

        $job = new SendCampaignJob($campaign);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);
        $whatsappService->shouldReceive('sendMessage')
            ->andReturn(['success' => true]);

        $job->handle($whatsappService);

        $campaign->refresh();

        $this->assertEquals(2, $campaign->total_contacts);
        $this->assertEquals(2, $campaign->sent_count);
        $this->assertEquals(0, $campaign->failed_count);
        $this->assertEquals('completed', $campaign->status);
    }

    /** @test */
    public function it_filters_contacts_by_ids_when_provided()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        $contact1 = Contact::factory()->create(['phone' => '+5511999999991']);
        $contact2 = Contact::factory()->create(['phone' => '+5511888888882']);
        $contact3 = Contact::factory()->create(['phone' => '+5511777777773']);

        $job = new SendCampaignJob($campaign, [$contact1->id, $contact2->id]);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);
        $whatsappService->shouldReceive('sendMessage')
            ->andReturn(['success' => true]);

        $job->handle($whatsappService);

        // Verificar se apenas os contatos especificados foram processados
        $this->assertEquals(2, CampaignLog::where('campaign_id', $campaign->id)->count());
        $this->assertDatabaseHas('campaign_logs', ['contact_id' => $contact1->id]);
        $this->assertDatabaseHas('campaign_logs', ['contact_id' => $contact2->id]);
        $this->assertDatabaseMissing('campaign_logs', ['contact_id' => $contact3->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 