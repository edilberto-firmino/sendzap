<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Jobs\SendCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    /**
     * Lista as campanhas.
     */
    public function index()
    {
        $campaigns = Campaign::latest()->paginate(20);
        return view('campaigns.index', compact('campaigns'));
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        return view('campaigns.create');
    }

    /**
     * Salva uma nova campanha.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_text' => 'required|string',
            'image_url' => 'nullable|url',
            'attachment_url' => 'nullable|url',
            'link_url' => 'nullable|url',
            'status' => 'required|in:draft,active,paused,completed',
            'scheduled_at' => 'nullable|date',
        ]);

        $validated['created_by'] = 1; // Temporário até ter auth
        $validated['total_contacts'] = Contact::count();

        Campaign::create($validated);

        return redirect()->route('campaigns.index')->with('success', 'Campanha criada com sucesso!');
    }

    /**
     * Exibe uma campanha específica.
     */
    public function show(Campaign $campaign)
    {
        $logs = $campaign->logs()->with('contact')->paginate(50);
        return view('campaigns.show', compact('campaign', 'logs'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(Campaign $campaign)
    {
        return view('campaigns.edit', compact('campaign'));
    }

    /**
     * Atualiza a campanha.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_text' => 'required|string',
            'image_url' => 'nullable|url',
            'attachment_url' => 'nullable|url',
            'link_url' => 'nullable|url',
            'status' => 'required|in:draft,active,paused,completed',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign->update($validated);

        return redirect()->route('campaigns.index')->with('success', 'Campanha atualizada com sucesso!');
    }

    /**
     * Remove a campanha.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Campanha removida com sucesso!');
    }

    /**
     * Exibe tela para selecionar campanha para disparo.
     */
    public function selectForDispatch()
    {
        $campaigns = Campaign::whereIn('status', ['draft', 'active', 'paused'])
                            ->where('total_contacts', '>', 0)
                            ->latest()
                            ->get();
        
        return view('campaigns.select-dispatch', compact('campaigns'));
    }

    /**
     * Exibe formulário para disparar campanha.
     */
    public function dispatchForm(Campaign $campaign)
    {
        $totalContacts = Contact::count();
        $validContacts = Contact::where('phone', 'like', '+%')->count();
        
        return view('campaigns.dispatch', compact('campaign', 'totalContacts', 'validContacts'));
    }

    /**
     * Dispara a campanha usando o sistema WhatsApp.
     */
    public function dispatch(Campaign $campaign)
    {
        if (!$campaign->canBeSent()) {
            return redirect()->back()->with('error', 'Campanha não pode ser enviada!');
        }

        try {
            // Disparar job em background
            SendCampaignJob::dispatch($campaign);

            return redirect()->route('campaigns.show', $campaign)
                ->with('success', 'Campanha enviada para processamento! Verifique os logs para acompanhar o progresso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar campanha: ' . $e->getMessage());
        }
    }

    /**
     * Exibe relatórios da campanha.
     */
    public function reports(Campaign $campaign)
    {
        $stats = [
            'total' => $campaign->total_contacts,
            'sent' => $campaign->sent_count,
            'failed' => $campaign->failed_count,
            'success_rate' => $campaign->success_rate,
            'pending' => $campaign->logs()->pending()->count(),
            'delivered' => $campaign->logs()->delivered()->count(),
            'read' => $campaign->logs()->read()->count(),
        ];

        return view('campaigns.reports', compact('campaign', 'stats'));
    }
}
