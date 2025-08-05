{{-- resources/views/campaigns/reports.blade.php --}}
@extends('layouts.app')

@section('title', 'Relatórios - ' . $campaign->name . ' - SendZap')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Relatórios: {{ $campaign->name }}</h1>
        <div class="btn-group">
            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline-primary">Voltar à Campanha</a>
            <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Lista de Campanhas</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Resumo Geral -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $stats['total'] }}</h3>
                    <p class="card-text">Total de Contatos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $stats['sent'] }}</h3>
                    <p class="card-text">Enviados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $stats['failed'] }}</h3>
                    <p class="card-text">Falharam</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['success_rate'] }}%</h3>
                    <p class="card-text">Taxa de Sucesso</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Detalhadas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status de Entrega</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 text-warning">{{ $stats['pending'] }}</div>
                            <small class="text-muted">Pendentes</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-info">{{ $stats['delivered'] }}</div>
                            <small class="text-muted">Entregues</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-success">{{ $stats['read'] }}</div>
                            <small class="text-muted">Lidas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informações da Campanha</h5>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'draft' ? 'secondary' : ($campaign->status === 'paused' ? 'warning' : 'info')) }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </p>
                    <p><strong>Criada em:</strong> {{ $campaign->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Última atualização:</strong> {{ $campaign->updated_at->format('d/m/Y H:i') }}</p>
                    @if($campaign->scheduled_at)
                        <p><strong>Agendada para:</strong> {{ $campaign->scheduled_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Progresso -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Progresso da Campanha</h5>
        </div>
        <div class="card-body">
            @if($stats['total'] > 0)
                <div class="progress mb-3" style="height: 30px;">
                    @php
                        $sentPercent = ($stats['sent'] / $stats['total']) * 100;
                        $failedPercent = ($stats['failed'] / $stats['total']) * 100;
                        $pendingPercent = 100 - $sentPercent - $failedPercent;
                    @endphp
                    
                    <div class="progress-bar bg-success" style="width: {{ $sentPercent }}%">
                        {{ $stats['sent'] }} Enviados
                    </div>
                    <div class="progress-bar bg-danger" style="width: {{ $failedPercent }}%">
                        {{ $stats['failed'] }} Falharam
                    </div>
                    <div class="progress-bar bg-warning" style="width: {{ $pendingPercent }}%">
                        {{ $stats['total'] - $stats['sent'] - $stats['failed'] }} Pendentes
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-md-4">
                        <small class="text-muted">Enviados: {{ number_format($sentPercent, 1) }}%</small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Falharam: {{ number_format($failedPercent, 1) }}%</small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Pendentes: {{ number_format($pendingPercent, 1) }}%</small>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">Nenhum contato associado a esta campanha.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Logs Detalhados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Histórico Detalhado de Envios</h5>
        </div>
        <div class="card-body">
            @php
                $logs = $campaign->logs()->with('contact')->orderBy('created_at', 'desc')->paginate(50);
            @endphp
            
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
                                <th>ID WhatsApp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->contact->name ?? 'N/A' }}</td>
                                    <td>{{ $log->contact->phone ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : ($log->status === 'pending' ? 'warning' : 'info')) }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->sent_at ? $log->sent_at->format('d/m/Y H:i:s') : '-' }}</td>
                                    <td>
                                        @if($log->error_message)
                                            <span class="text-danger" title="{{ $log->error_message }}">
                                                {{ Str::limit($log->error_message, 30) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $log->whatsapp_message_id ?: '-' }}</td>
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