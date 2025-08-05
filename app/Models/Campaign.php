<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'message_text',
        'image_url',
        'attachment_url',
        'link_url',
        'status',
        'scheduled_at',
        'created_by',
        'total_contacts',
        'sent_count',
        'failed_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'total_contacts' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /**
     * Relacionamento com os logs da campanha
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    /**
     * Relacionamento com os contatos através dos logs
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'campaign_logs')
                    ->withPivot('status', 'sent_at', 'error_message')
                    ->withTimestamps();
    }

    /**
     * Scope para campanhas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para campanhas em rascunho
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope para campanhas agendadas
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at');
    }

    /**
     * Calcular taxa de sucesso
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_contacts === 0) {
            return 0;
        }
        
        return round(($this->sent_count / $this->total_contacts) * 100, 2);
    }

    /**
     * Verificar se a campanha pode ser enviada
     */
    public function canBeSent()
    {
        return in_array($this->status, ['draft', 'paused']) && $this->total_contacts > 0;
    }
}
