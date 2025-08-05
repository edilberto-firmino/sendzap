{{-- resources/views/campaigns/show.blade.php --}}
@extends('layouts.app')

@section('title', $campaign->name . ' - SendZap')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $campaign->name }}</h1>
        <div class="btn-group">
            <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-warning">Editar</a>
            @if($campaign->canBeSent())
                <a href="{{ route('campaigns.dispatch.form', $campaign) }}" class="btn btn-success">🚀 Disparar</a>
            @endif
            <a href="{{ route('campaigns.reports', $campaign) }}" class="btn btn-info">📊 Relatórios</a>
            <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Informações da Campanha -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detalhes da Campanha</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'draft' ? 'secondary' : ($campaign->status === 'paused' ? 'warning' : 'info')) }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </p>
                            <p><strong>Criada em:</strong> {{ $campaign->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Atualizada em:</strong> {{ $campaign->updated_at->format('d/m/Y H:i') }}</p>
                            @if($campaign->scheduled_at)
                                <p><strong>Agendada para:</strong> {{ $campaign->scheduled_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total de contatos:</strong> {{ $campaign->total_contacts }}</p>
                            <p><strong>Enviados:</strong> {{ $campaign->sent_count }}</p>
                            <p><strong>Falharam:</strong> {{ $campaign->failed_count }}</p>
                            <p><strong>Taxa de sucesso:</strong> {{ $campaign->success_rate }}%</p>
                        </div>
                    </div>

                    @if($campaign->description)
                        <hr>
                        <p><strong>Descrição:</strong></p>
                        <p>{{ $campaign->description }}</p>
                    @endif

                    <hr>
                    <p><strong>Mensagem:</strong></p>
                    <div class="bg-light p-3 rounded">
                        {{ $campaign->message_text }}
                    </div>

                    @if($campaign->image_url || $campaign->attachment_url || $campaign->link_url)
                        <hr>
                        <p><strong>Mídia:</strong></p>
                        <div class="row">
                            @if($campaign->image_url)
                                <div class="col-md-4">
                                    <p><strong>Imagem:</strong></p>
                                    <a href="{{ $campaign->image_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Ver Imagem</a>
                                </div>
                            @endif
                            @if($campaign->attachment_url)
                                <div class="col-md-4">
                                    <p><strong>Anexo:</strong></p>
                                    <a href="{{ $campaign->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Baixar Anexo</a>
                                </div>
                            @endif
                            @if($campaign->link_url)
                                <div class="col-md-4">
                                    <p><strong>Link:</strong></p>
                                    <a href="{{ $campaign->link_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Abrir Link</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Estatísticas</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h4 text-primary">{{ $campaign->total_contacts }}</div>
                        <small class="text-muted">Total de Contatos</small>
                    </div>
                    <div class="text-center mb-3">
                        <div class="h4 text-success">{{ $campaign->sent_count }}</div>
                        <small class="text-muted">Enviados</small>
                    </div>
                    <div class="text-center mb-3">
                        <div class="h4 text-danger">{{ $campaign->failed_count }}</div>
                        <small class="text-muted">Falharam</small>
                    </div>
                    <div class="text-center">
                        <div class="h4 text-info">{{ $campaign->success_rate }}%</div>
                        <small class="text-muted">Taxa de Sucesso</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs de Envio -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Histórico de Envios</h5>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Contato</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th>Enviado em</th>
                                <th>Erro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->contact->name }}</td>
                                    <td>{{ $log->contact->phone }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : ($log->status === 'pending' ? 'warning' : 'info')) }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->sent_at ? $log->sent_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $log->error_message ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">Nenhum envio registrado ainda.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 