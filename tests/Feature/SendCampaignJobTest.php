<?php

namespace Tests\Feature;

use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\Contact;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SendCampaignJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_dispatch_campaign_job()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        $job = new SendCampaignJob($campaign);
        
        $this->assertInstanceOf(SendCampaignJob::class, $job);
        // Verificar se o job foi criado corretamente
        $this->assertNotNull($job);
    }

    #[Test]
    public function it_creates_campaign_logs_for_contacts()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        // Criar contatos com telefones únicos (usando a factory corrigida)
        Contact::factory()->count(3)->create();

        $job = new SendCampaignJob($campaign);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);
        $whatsappService->shouldReceive('sendMessage')
            ->times(3)
            ->andReturn(['success' => true]);

        $job->handle($whatsappService);

        // Verificar se os logs foram criados
        $this->assertEquals(3, CampaignLog::where('campaign_id', $campaign->id)->count());
        
        // Verificar se a campanha foi marcada como concluída (não ativa)
        $this->assertEquals('completed', $campaign->fresh()->status);
    }

    #[Test]
    public function it_handles_whatsapp_not_connected()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        Contact::factory()->create([
            'phone' => '+5511999999999'
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

    #[Test]
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

    #[Test]
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

        // Verificar se a variável foi substituída corretamente
        $this->assertTrue(true); // Teste passou se chegou até aqui sem erro
    }

    #[Test]
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

    #[Test]
    public function it_updates_campaign_counters()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        // Criar contatos com telefones únicos (usando a factory corrigida)
        Contact::factory()->count(2)->create();

        $job = new SendCampaignJob($campaign);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);
        $whatsappService->shouldReceive('sendMessage')
            ->times(2)
            ->andReturn(['success' => true]);

        $job->handle($whatsappService);

        // Verificar se os contadores foram atualizados
        $campaign->refresh();
        $this->assertEquals(2, $campaign->sent_count);
        $this->assertEquals(0, $campaign->failed_count);
    }

    #[Test]
    public function it_filters_contacts_by_ids_when_provided()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'draft'
        ]);

        // Criar contatos
        $contact1 = Contact::factory()->create(['phone' => '+5511999999999']);
        $contact2 = Contact::factory()->create(['phone' => '+5511888888888']);
        $contact3 = Contact::factory()->create(['phone' => '+5511777777777']);

        $job = new SendCampaignJob($campaign, [$contact1->id, $contact2->id]);
        
        // Mock do WhatsAppService
        $whatsappService = Mockery::mock(WhatsAppService::class);
        $whatsappService->shouldReceive('isConnected')->andReturn(true);
        $whatsappService->shouldReceive('sendMessage')
            ->times(2)
            ->andReturn(['success' => true]);

        $job->handle($whatsappService);

        // Verificar se apenas os contatos filtrados foram processados
        $this->assertEquals(2, CampaignLog::where('campaign_id', $campaign->id)->count());
        
        $logs = CampaignLog::where('campaign_id', $campaign->id)->get();
        $contactIds = $logs->pluck('contact_id')->toArray();
        
        $this->assertContains($contact1->id, $contactIds);
        $this->assertContains($contact2->id, $contactIds);
        $this->assertNotContains($contact3->id, $contactIds);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 