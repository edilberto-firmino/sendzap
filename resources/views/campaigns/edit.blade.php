{{-- resources/views/campaigns/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Campanha - SendZap')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Editar Campanha: {{ $campaign->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('campaigns.update', $campaign) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Campanha *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $campaign->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message_text" class="form-label">Mensagem *</label>
                            <textarea class="form-control @error('message_text') is-invalid @enderror" 
                                      id="message_text" name="message_text" rows="6" required>{{ old('message_text', $campaign->message_text) }}</textarea>
                            <div class="form-text">
                                <strong>Variáveis disponíveis:</strong> {nome}, {email}, {cidade}, {estado}
                            </div>
                            @error('message_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">URL da Imagem</label>
                                    <input type="url" class="form-control @error('image_url') is-invalid @enderror" 
                                           id="image_url" name="image_url" value="{{ old('image_url', $campaign->image_url) }}">
                                    @error('image_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="attachment_url" class="form-label">URL do Anexo</label>
                                    <input type="url" class="form-control @error('attachment_url') is-invalid @enderror" 
                                           id="attachment_url" name="attachment_url" value="{{ old('attachment_url', $campaign->attachment_url) }}">
                                    @error('attachment_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="link_url" class="form-label">URL do Link</label>
                                    <input type="url" class="form-control @error('link_url') is-invalid @enderror" 
                                           id="link_url" name="link_url" value="{{ old('link_url', $campaign->link_url) }}">
                                    @error('link_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $campaign->status) == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                        <option value="active" {{ old('status', $campaign->status) == 'active' ? 'selected' : '' }}>Ativa</option>
                                        <option value="paused" {{ old('status', $campaign->status) == 'paused' ? 'selected' : '' }}>Pausada</option>
                                        <option value="completed" {{ old('status', $campaign->status) == 'completed' ? 'selected' : '' }}>Concluída</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scheduled_at" class="form-label">Agendar Envio</label>
                                    <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" 
                                           id="scheduled_at" name="scheduled_at" 
                                           value="{{ old('scheduled_at', $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                                    @error('scheduled_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar Campanha</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 