{{-- resources/views/campaigns/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Campanhas - SendZap')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Campanhas</h1>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">+ Nova Campanha</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        @forelse($campaigns as $campaign)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $campaign->name }}</h5>
                        <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'draft' ? 'secondary' : ($campaign->status === 'paused' ? 'warning' : 'info')) }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted">{{ Str::limit($campaign->description, 100) }}</p>
                        
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <small class="text-muted">Total</small>
                                <div class="fw-bold">{{ $campaign->total_contacts }}</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Enviados</small>
                                <div class="fw-bold text-success">{{ $campaign->sent_count }}</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Taxa</small>
                                <div class="fw-bold">{{ $campaign->success_rate }}%</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline-primary btn-sm">Ver Detalhes</a>
                            
                            @if($campaign->canBeSent())
                                <a href="{{ route('campaigns.dispatch.form', $campaign) }}" class="btn btn-success btn-sm">🚀 Disparar</a>
                            @endif
                            
                            <div class="btn-group" role="group">
                                <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-warning btn-sm">Editar</a>
                                <a href="{{ route('campaigns.reports', $campaign) }}" class="btn btn-info btn-sm">📊 Relatórios</a>
                                <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Tem certeza que deseja excluir esta campanha?')" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Criada em {{ $campaign->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <h4 class="text-muted">Nenhuma campanha encontrada</h4>
                    <p class="text-muted">Crie sua primeira campanha para começar a enviar mensagens!</p>
                    <a href="{{ route('campaigns.create') }}" class="btn btn-primary">Criar Campanha</a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $campaigns->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection 