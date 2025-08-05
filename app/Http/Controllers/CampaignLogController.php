<?php

namespace App\Http\Controllers;

use App\Models\CampaignLog;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignLogController extends Controller
{
    /**
     * Lista os logs de uma campanha.
     */
    public function index(Campaign $campaign)
    {
        $logs = $campaign->logs()->with('contact')->paginate(100);
        return view('campaigns.logs.index', compact('campaign', 'logs'));
    }

    /**
     * Exibe detalhes de um log específico.
     */
    public function show(CampaignLog $log)
    {
        return view('campaigns.logs.show', compact('log'));
    }

    /**
     * Atualiza status de um log (para webhooks do n8n).
     */
    public function updateStatus(Request $request, CampaignLog $log)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,sent,failed,delivered,read',
            'whatsapp_message_id' => 'nullable|string',
            'error_message' => 'nullable|string',
        ]);

        switch ($validated['status']) {
            case 'sent':
                $log->markAsSent($validated['whatsapp_message_id'] ?? null);
                break;
            case 'failed':
                $log->markAsFailed($validated['error_message'] ?? null);
                break;
            case 'delivered':
                $log->markAsDelivered();
                break;
            case 'read':
                $log->markAsRead();
                break;
        }

        return response()->json(['success' => true]);
    }
}
