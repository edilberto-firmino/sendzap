<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\Contact;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora
    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1min, 5min, 10min
    public $chunkSize = 100; // Máximo de mensagens por lote

    private Campaign $campaign;
    private ?array $contactIds;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign, ?array $contactIds = null)
    {
        $this->campaign = $campaign;
        $this->contactIds = $contactIds;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            Log::info('Iniciando envio da campanha', [
                'campaign_id' => $this->campaign->id,
                'campaign_name' => $this->campaign->name
            ]);

            // Verificar se WhatsApp está conectado
            if (!$whatsappService->isConnected()) {
                throw new \Exception('WhatsApp não está conectado');
            }

            // Buscar contatos
            $contacts = $this->getContacts();
            
            if ($contacts->isEmpty()) {
                Log::warning('Nenhum contato encontrado para a campanha', [
                    'campaign_id' => $this->campaign->id
                ]);
                return;
            }

            // Criar logs para todos os contatos
            $this->createCampaignLogs($contacts);

            // Atualizar contadores da campanha
            $this->campaign->update([
                'total_contacts' => $contacts->count(),
                'status' => 'active'
            ]);

            // Enviar mensagens
            $this->sendMessages($whatsappService, $contacts);

            // Marcar campanha como concluída
            $this->campaign->update(['status' => 'completed']);

            Log::info('Campanha enviada com sucesso', [
                'campaign_id' => $this->campaign->id,
                'total_contacts' => $contacts->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar campanha', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage()
            ]);

            // Marcar campanha como pausada em caso de erro
            $this->campaign->update(['status' => 'paused']);

            throw $e;
        }
    }

    /**
     * Obter contatos para a campanha
     */
    private function getContacts()
    {
        $query = Contact::where('phone', 'like', '+%');

        // Filtrar por IDs específicos se fornecidos
        if ($this->contactIds) {
            $query->whereIn('id', $this->contactIds);
        }

        return $query->get();
    }

    /**
     * Criar logs da campanha
     */
    private function createCampaignLogs($contacts): void
    {
        $logs = [];
        
        foreach ($contacts as $contact) {
            $logs[] = [
                'campaign_id' => $this->campaign->id,
                'contact_id' => $contact->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Inserir em lote para melhor performance
        CampaignLog::insert($logs);
    }

    /**
     * Enviar mensagens
     */
    private function sendMessages(WhatsAppService $whatsappService, $contacts): void
    {
        $sentCount = 0;
        $failedCount = 0;

        foreach ($contacts as $contact) {
            try {
                // Buscar log da campanha
                $log = CampaignLog::where('campaign_id', $this->campaign->id)
                    ->where('contact_id', $contact->id)
                    ->first();

                if (!$log) {
                    Log::warning('Log da campanha não encontrado', [
                        'campaign_id' => $this->campaign->id,
                        'contact_id' => $contact->id
                    ]);
                    continue;
                }

                // Construir mensagem
                $message = $this->buildMessage($contact);

                // Enviar mensagem
                $result = $whatsappService->sendMessage(
                    $contact->phone,
                    $message,
                    $log->id
                );

                if ($result['success']) {
                    $sentCount++;
                } else {
                    $failedCount++;
                    Log::warning('Falha ao enviar mensagem', [
                        'contact_id' => $contact->id,
                        'phone' => $contact->phone,
                        'error' => $result['error'] ?? 'Erro desconhecido'
                    ]);
                }

                // Atualizar contadores da campanha
                $this->campaign->update([
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ]);

                // Delay para evitar bloqueio
                sleep(2);

            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Erro ao processar contato', [
                    'contact_id' => $contact->id,
                    'error' => $e->getMessage()
                ]);

                // Marcar log como falhou
                $log = CampaignLog::where('campaign_id', $this->campaign->id)
                    ->where('contact_id', $contact->id)
                    ->first();

                if ($log) {
                    $log->markAsFailed($e->getMessage());
                }
            }
        }

        Log::info('Envio da campanha concluído', [
            'campaign_id' => $this->campaign->id,
            'sent' => $sentCount,
            'failed' => $failedCount
        ]);
    }

    /**
     * Construir mensagem personalizada
     */
    private function buildMessage(Contact $contact): string
    {
        $message = $this->campaign->message_text;

        // Substituir variáveis
        $replacements = [
            '{nome}' => $contact->name,
            '{nome_social}' => $contact->social_name ?? $contact->name,
            '{cidade}' => $contact->city ?? '',
            '{estado}' => $contact->state ?? '',
            '{idade}' => $contact->age ?? '',
            '{genero}' => $this->getGenderText($contact->gender),
            '{telefone}' => $contact->phone,
            '{email}' => $contact->email ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        return $message;
    }

    /**
     * Obter texto do gênero
     */
    private function getGenderText(?string $gender): string
    {
        return match ($gender) {
            'male' => 'masculino',
            'female' => 'feminino',
            'other' => 'outro',
            default => ''
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de envio de campanha falhou', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage()
        ]);

        // Marcar campanha como pausada
        $this->campaign->update(['status' => 'paused']);
    }
} 