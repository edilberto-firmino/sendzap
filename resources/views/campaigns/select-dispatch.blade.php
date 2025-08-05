{{-- resources/views/campaigns/select-dispatch.blade.php --}}
@extends('layouts.app')

@section('title', 'Selecionar Campanha para Disparo - SendZap')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">🚀 Selecionar Campanha para Disparo</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($campaigns->count() > 0)
                        <div class="alert alert-info">
                            <h5>📋 Campanhas Disponíveis para Disparo</h5>
                            <p class="mb-0">Selecione uma campanha abaixo para iniciar o processo de envio.</p>
                            <div class="mt-2">
                                <small>
                                    <strong>Status disponíveis:</strong> 
                                    <span class="badge bg-secondary">Rascunho</span> (nova campanha), 
                                    <span class="badge bg-success">Ativa</span> (pode ser reenviada), 
                                    <span class="badge bg-warning">Pausada</span> (pode ser retomada)
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($campaigns as $campaign)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="card-title mb-0">{{ $campaign->name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text text-muted">{{ Str::limit($campaign->description, 80) }}</p>
                                            
                                            <div class="mb-3">
                                                <strong>📱 Mensagem:</strong>
                                                <p class="small">{{ Str::limit($campaign->message_text, 100) }}</p>
                                            </div>

                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="bg-light p-2 rounded">
                                                        <small class="text-muted d-block">Contatos</small>
                                                        <strong class="text-primary">{{ $campaign->total_contacts }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light p-2 rounded">
                                                        <small class="text-muted d-block">Status</small>
                                                        <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'draft' ? 'secondary' : 'warning') }}">
                                                            {{ ucfirst($campaign->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($campaign->image_url)
                                                <div class="mb-2">
                                                    <small class="text-muted">📷 Imagem: Sim</small>
                                                </div>
                                            @endif

                                            @if($campaign->attachment_url)
                                                <div class="mb-2">
                                                    <small class="text-muted">📎 Anexo: Sim</small>
                                                </div>
                                            @endif

                                            @if($campaign->link_url)
                                                <div class="mb-2">
                                                    <small class="text-muted">🔗 Link: Sim</small>
                                                </div>
                                            @endif

                                            <div class="d-grid">
                                                <a href="{{ route('campaigns.dispatch.form', $campaign) }}" 
                                                   class="btn btn-success">
                                                    🚀 Disparar Esta Campanha
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted">
                                            <small>Criada em {{ $campaign->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">
                                ← Voltar para Lista de Campanhas
                            </a>
                        </div>

                    @else
                        <div class="text-center py-5">
                            <div class="alert alert-warning">
                                <h4>📋 Nenhuma campanha disponível para disparo</h4>
                                <p class="mb-3">Para disparar uma campanha, ela precisa atender aos seguintes critérios:</p>
                                <ul class="text-start">
                                    <li><strong>Status:</strong> "Rascunho", "Ativa" ou "Pausada"</li>
                                    <li><strong>Contatos:</strong> Ter contatos cadastrados no sistema</li>
                                    <li><strong>Mensagem:</strong> Ter uma mensagem configurada</li>
                                </ul>
                                
                                <div class="mt-4">
                                    <a href="{{ route('campaigns.create') }}" class="btn btn-primary me-2">
                                        + Criar Nova Campanha
                                    </a>
                                    <a href="{{ route('contacts.index') }}" class="btn btn-info">
                                        📇 Gerenciar Contatos
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 