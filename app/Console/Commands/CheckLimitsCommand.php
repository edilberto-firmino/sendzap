<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CampaignLog;
use Carbon\Carbon;

class CheckLimitsCommand extends Command
{
    protected $signature = 'sendzap:check-limits';
    protected $description = 'Verificar limites de disparo do WhatsApp';

    public function handle()
    {
        $this->info('📊 Verificando Limites de Disparo');
        $this->line('=====================================');

        // Limites configurados
        $dailyLimit = config('services.whatsapp.daily_limit', 1000);
        $hourlyLimit = config('services.whatsapp.hourly_limit', 100);
        $messageDelay = config('services.whatsapp.message_delay', 2);

        $this->info("📋 Limites Configurados:");
        $this->line("   • Diário: {$dailyLimit} mensagens");
        $this->line("   • Por hora: {$hourlyLimit} mensagens");
        $this->line("   • Delay: {$messageDelay} segundos");

        // Estatísticas do dia
        $todaySent = CampaignLog::where('status', 'sent')
            ->whereDate('sent_at', Carbon::today())
            ->count();

        $todayFailed = CampaignLog::where('status', 'failed')
            ->whereDate('sent_at', Carbon::today())
            ->count();

        $this->info("\n📈 Estatísticas de Hoje:");
        $this->line("   • Enviadas: {$todaySent}");
        $this->line("   • Falharam: {$todayFailed}");
        $this->line("   • Total: " . ($todaySent + $todayFailed));

        // Estatísticas da última hora
        $lastHourSent = CampaignLog::where('status', 'sent')
            ->where('sent_at', '>=', Carbon::now()->subHour())
            ->count();

        $this->info("\n⏰ Última Hora:");
        $this->line("   • Enviadas: {$lastHourSent}");

        // Verificar se está dentro dos limites
        $dailyRemaining = $dailyLimit - $todaySent;
        $hourlyRemaining = $hourlyLimit - $lastHourSent;

        $this->info("\n✅ Status dos Limites:");
        
        if ($dailyRemaining > 0) {
            $this->line("   • Diário: ✅ {$dailyRemaining} mensagens restantes");
        } else {
            $this->error("   • Diário: ❌ Limite diário atingido!");
        }

        if ($hourlyRemaining > 0) {
            $this->line("   • Por hora: ✅ {$hourlyRemaining} mensagens restantes");
        } else {
            $this->error("   • Por hora: ❌ Limite horário atingido!");
        }

        // Recomendações
        $this->info("\n💡 Recomendações:");
        
        if ($dailyRemaining < 100) {
            $this->warn("   • ⚠️ Limite diário quase atingido!");
        }
        
        if ($hourlyRemaining < 10) {
            $this->warn("   • ⚠️ Limite horário quase atingido!");
        }

        if ($todaySent > 0) {
            $avgPerHour = round($todaySent / max(1, Carbon::now()->hour), 2);
            $this->line("   • Média por hora: {$avgPerHour} mensagens");
        }

        $this->info("\n🎯 Para verificar em tempo real:");
        $this->line("   php artisan sendzap:check-limits");
    }
} 