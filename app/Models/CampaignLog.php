<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'status',
        'sent_at',
        'error_message',
        'whatsapp_message_id',
        'message_data',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'message_data' => 'array',
    ];

    /**
     * Relacionamento com a campanha
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Relacionamento com o contato
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Scope para logs enviados com sucesso
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope para logs que falharam
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para logs pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para logs entregues
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope para logs lidos
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Marcar como enviado
     */
    public function markAsSent($whatsappMessageId = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'whatsapp_message_id' => $whatsappMessageId,
        ]);
    }

    /**
     * Marcar como falhou
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Marcar como entregue
     */
    public function markAsDelivered()
    {
        $this->update(['status' => 'delivered']);
    }

    /**
     * Marcar como lido
     */
    public function markAsRead()
    {
        $this->update(['status' => 'read']);
    }
}
