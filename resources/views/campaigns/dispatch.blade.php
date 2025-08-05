{{-- resources/views/campaigns/dispatch.blade.php --}}
@extends('layouts.app')

@section('title', 'Disparar Campanha - ' . $campaign->name . ' - SendZap')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">🚀 Disparar Campanha: {{ $campaign->name }}</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Resumo da Campanha -->
                    <div class="alert alert-info">
                        <h5>📋 Resumo da Campanha</h5>
                        <p><strong>Mensagem:</strong> {{ Str::limit($campaign->message_text, 100) }}</p>
                        <p><strong>Status atual:</strong> 
                            <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'draft' ? 'secondary' : ($campaign->status === 'paused' ? 'warning' : 'info')) }}">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </p>
                    </div>

                    <!-- Estatísticas de Contatos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-primary">{{ $totalContacts }}</h3>
                                    <p class="card-text">Total de Contatos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-success">{{ $validContacts }}</h3>
                                    <p class="card-text">Contatos com WhatsApp</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($validContacts > 0)
                        <!-- Avisos -->
                        <div class="alert alert-warning">
                            <h6>⚠️ Avisos Importantes:</h6>
                            <ul class="mb-0">
                                <li>Esta ação enviará mensagens para <strong>{{ $validContacts }} contatos</strong></li>
                                <li>O processo pode levar alguns minutos dependendo da quantidade</li>
                                <li>Certifique-se de que sua conexão com WhatsApp está ativa</li>
                                <li>As mensagens serão enviadas através do n8n</li>
                            </ul>
                        </div>

                        <!-- Preview da Mensagem -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">📱 Preview da Mensagem</h6>
                            </div>
                            <div class="card-body">
                                <div class="bg-light p-3 rounded">
                                    <p class="mb-2"><strong>Exemplo para: João Silva</strong></p>
                                    <p class="mb-0">{{ str_replace('{nome}', 'João Silva', $campaign->message_text) }}</p>
                                </div>
                                
                                @if($campaign->image_url)
                                    <div class="mt-3">
                                        <p><strong>Imagem:</strong></p>
                                        <img src="{{ $campaign->image_url }}" alt="Imagem da campanha" class="img-fluid" style="max-height: 200px;">
                                    </div>
                                @endif
                                
                                @if($campaign->attachment_url)
                                    <div class="mt-3">
                                        <p><strong>Anexo:</strong> <a href="{{ $campaign->attachment_url }}" target="_blank">Ver anexo</a></p>
                                    </div>
                                @endif
                                
                                @if($campaign->link_url)
                                    <div class="mt-3">
                                        <p><strong>Link:</strong> <a href="{{ $campaign->link_url }}" target="_blank">{{ $campaign->link_url }}</a></p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Formulário de Confirmação -->
                        <form action="{{ route('campaigns.dispatch', $campaign) }}" method="POST">
                            @csrf
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirm" required>
                                <label class="form-check-label" for="confirm">
                                    <strong>Confirmo que desejo disparar esta campanha para {{ $validContacts }} contatos</strong>
                                </label>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success" id="dispatchBtn" disabled>
                                    🚀 Iniciar Disparo
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            <h6>❌ Não é possível disparar esta campanha</h6>
                            <p class="mb-0">Não há contatos válidos com WhatsApp cadastrados no sistema.</p>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-secondary">Voltar</a>
                            <a href="{{ route('contacts.create') }}" class="btn btn-primary">Adicionar Contatos</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('confirm').addEventListener('change', function() {
    document.getElementById('dispatchBtn').disabled = !this.checked;
});
</script>
@endsection 