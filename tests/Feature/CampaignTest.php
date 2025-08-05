<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Desabilitar todos os middlewares para testes
        $this->withoutMiddleware();
    }

    /**
     * Testa se a página de listagem de campanhas carrega.
     */
    public function test_can_view_campaigns_index(): void
    {
        $response = $this->get('/campaigns');

        $response->assertStatus(200);
        $response->assertSee('Campanhas');
    }

    /**
     * Testa se a página de criação de campanha carrega.
     */
    public function test_can_view_create_campaign_form(): void
    {
        $response = $this->get('/campaigns/create');

        $response->assertStatus(200);
        $response->assertSee('Nova Campanha');
        $response->assertSee('Nome da Campanha');
    }

    /**
     * Testa se pode criar uma campanha.
     */
    public function test_can_create_campaign(): void
    {
        // Criar alguns contatos para o teste
        Contact::factory()->count(5)->create();

        $campaignData = [
            'name' => 'Campanha de Teste',
            'description' => 'Descrição da campanha de teste',
            'message_text' => 'Olá {nome}, esta é uma mensagem de teste!',
            'status' => 'draft',
        ];

        $response = $this->withoutMiddleware()
            ->post('/campaigns', $campaignData);

        $response->assertRedirect('/campaigns');
        $response->assertSessionHas('success');

        // Verifica se a campanha foi criada no banco
        $this->assertDatabaseHas('campaigns', [
            'name' => 'Campanha de Teste',
            'status' => 'draft',
        ]);
    }

    /**
     * Testa se não pode criar campanha sem nome.
     */
    public function test_cannot_create_campaign_without_name(): void
    {
        $campaignData = [
            'description' => 'Descrição da campanha',
            'message_text' => 'Mensagem de teste',
            'status' => 'draft',
        ];

        $response = $this->withoutMiddleware()
            ->post('/campaigns', $campaignData);

        $response->assertSessionHasErrors(['name']);
        $response->assertStatus(302); // Redirect back with errors
    }

    /**
     * Testa se não pode criar campanha sem mensagem.
     */
    public function test_cannot_create_campaign_without_message(): void
    {
        $campaignData = [
            'name' => 'Campanha de Teste',
            'description' => 'Descrição da campanha',
            'status' => 'draft',
        ];

        $response = $this->withoutMiddleware()
            ->post('/campaigns', $campaignData);

        $response->assertSessionHasErrors(['message_text']);
        $response->assertStatus(302); // Redirect back with errors
    }

    /**
     * Testa se pode visualizar uma campanha específica.
     */
    public function test_can_view_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->get("/campaigns/{$campaign->id}");

        $response->assertStatus(200);
        $response->assertSee($campaign->name);
    }

    /**
     * Testa se pode editar uma campanha.
     */
    public function test_can_edit_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->get("/campaigns/{$campaign->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($campaign->name);
    }

    /**
     * Testa se pode atualizar uma campanha.
     */
    public function test_can_update_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $updateData = [
            'name' => 'Campanha Atualizada',
            'description' => 'Nova descrição',
            'message_text' => 'Nova mensagem',
            'status' => 'active',
        ];

        $response = $this->withoutMiddleware()
            ->put("/campaigns/{$campaign->id}", $updateData);

        $response->assertRedirect('/campaigns');
        $response->assertSessionHas('success');

        // Verifica se foi atualizada no banco
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Campanha Atualizada',
            'status' => 'active',
        ]);
    }

    /**
     * Testa se pode deletar uma campanha.
     */
    public function test_can_delete_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->withoutMiddleware()
            ->delete("/campaigns/{$campaign->id}");

        $response->assertRedirect('/campaigns');
        $response->assertSessionHas('success');

        // Verifica se foi deletada do banco
        $this->assertDatabaseMissing('campaigns', [
            'id' => $campaign->id,
        ]);
    }
}
