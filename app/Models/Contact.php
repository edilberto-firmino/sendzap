<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'cpf',
        'social_name',
        'birthdate',
        'city',
        'state',
        'gender',
        'age',
        'tags',
    ];
    
    protected $casts = [
        'tags' => 'array',
        'birthdate' => 'date',
    ];

    /**
     * Relacionamento com os logs de campanhas
     */
    public function campaignLogs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    /**
     * Relacionamento com as campanhas através dos logs
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_logs')
                    ->withPivot('status', 'sent_at', 'error_message')
                    ->withTimestamps();
    }
}
