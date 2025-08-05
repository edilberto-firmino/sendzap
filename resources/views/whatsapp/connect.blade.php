@extends('layouts.app')

@section('title', 'Conectar WhatsApp - SendZap')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fab fa-whatsapp text-success"></i>
                        Conectar WhatsApp
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Status do WhatsApp -->
                    <div class="alert alert-info">
                        <strong>Status:</strong> 
                        <span id="whatsappStatus">
                            <span class="badge bg-warning">Verificando...</span>
                        </span>
                    </div>

                    <!-- Seção QR Code -->
                    <div id="qrCodeSection" style="display: none;">
                        <div class="text-center mb-3">
                            <h5>📱 Escaneie o QR Code</h5>
                            <p class="text-muted">
                                Abra o WhatsApp no seu celular → Configurações → Aparelhos conectados → Conectar um aparelho
                            </p>
                        </div>
                        
                        <div class="text-center mb-3">
                            <div id="qrCode" class="border rounded p-3 bg-light">
                                <div class="alert alert-info">Aguardando QR Code...</div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button class="btn btn-primary" onclick="getQRCode()">
                                <i class="fas fa-sync-alt"></i> Atualizar QR Code
                            </button>
                            <button class="btn btn-info" onclick="checkStatus()">
                                <i class="fas fa-info-circle"></i> Verificar Status
                            </button>
                        </div>
                    </div>

                    <!-- Seção Conectado -->
                    <div id="connectedSection" style="display: none;">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> WhatsApp Conectado!</h5>
                            <p>Seu WhatsApp está conectado e pronto para enviar mensagens.</p>
                        </div>
                    </div>

                    <!-- Seção Teste de Mensagem -->
                    <div id="testMessageSection" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6>🧪 Teste de Mensagem</h6>
                            </div>
                            <div class="card-body">
                                <form id="testMessageForm">
                                    <div class="mb-3">
                                        <label for="testPhone" class="form-label">Número do Telefone (com código do país)</label>
                                        <input type="text" class="form-control" id="testPhone" placeholder="5511999999999" required>
                                        <div class="form-text">Exemplo: 5511999999999 (Brasil)</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="testMessage" class="form-label">Mensagem</label>
                                        <textarea class="form-control" id="testMessage" rows="3" placeholder="Olá! Esta é uma mensagem de teste do SendZap." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Enviar Teste
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="mt-4 text-center">
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary" onclick="forceReconnect()">
                                <i class="fas fa-sync-alt"></i> Reset/Reconectar
                            </button>
                            {{-- <button class="btn btn-info" onclick="checkStatus()">
                                <i class="fas fa-info-circle"></i> Verificar Status
                            </button> --}}
                        </div>
                        
                        <!-- Botão de emergência (oculto por padrão) -->
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-warning" onclick="showEmergencyOptions()">
                                <i class="fas fa-exclamation-triangle"></i> Opções de Emergência
                            </button>
                        </div>
                        
                        <div id="emergencyOptions" style="display: none;" class="mt-2">
                            <form action="{{ route('whatsapp.clear-auth') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('⚠️ ATENÇÃO: Isso irá limpar TODOS os dados de autenticação e forçar uma nova conexão. Use apenas se o Reset/Reconectar não funcionar. Tem certeza?')">
                                    <i class="fas fa-trash"></i> Limpar Autenticação (Emergência)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code gerado pelo servidor - sem dependências externas -->

<script>
// Variáveis globais
let statusCheckInterval;

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Página carregada, iniciando...');
    
    // Iniciar verificação de status imediatamente
    checkStatus();
    startStatusCheck();
});

// Verificar status do WhatsApp
function checkStatus() {
    console.log('🔍 Verificando status...');
    
    fetch('{{ route("whatsapp.status") }}')
        .then(response => response.json())
        .then(data => {
            console.log('📊 Status recebido:', data);
            updateDisplay(data);
        })
        .catch(error => {
            console.error('❌ Erro ao verificar status:', error);
            document.getElementById('whatsappStatus').innerHTML = '<span class="badge bg-danger">Erro</span>';
        });
}

// Atualizar display baseado no status
function updateDisplay(status) {
    const qrSection = document.getElementById('qrCodeSection');
    const connectedSection = document.getElementById('connectedSection');
    const testSection = document.getElementById('testMessageSection');
    const statusElement = document.getElementById('whatsappStatus');
    
    // Atualizar badge de status
    if (status.isConnected) {
        statusElement.innerHTML = '<span class="badge bg-success">Conectado</span>';
        qrSection.style.display = 'none';
        connectedSection.style.display = 'block';
        testSection.style.display = 'block';
        stopStatusCheck();
    } else if (status.status === 'qr_ready') {
        statusElement.innerHTML = '<span class="badge bg-warning">QR Code Pronto</span>';
        qrSection.style.display = 'block';
        connectedSection.style.display = 'none';
        testSection.style.display = 'none';
        getQRCode();
    } else {
        statusElement.innerHTML = '<span class="badge bg-secondary">Desconectado</span>';
        qrSection.style.display = 'none';
        connectedSection.style.display = 'none';
        testSection.style.display = 'none';
    }
}

// Obter QR Code
function getQRCode() {
    console.log('🔄 Buscando QR Code...');
    
    const qrContainer = document.getElementById('qrCode');
    qrContainer.innerHTML = '<div class="alert alert-info">Carregando QR Code...</div>';
    
    fetch('{{ route("whatsapp.qr") }}')
        .then(response => response.json())
        .then(data => {
            console.log('📊 Dados QR recebidos:', data);
            
            if (data.qr) {
                console.log('✅ QR Code encontrado, gerando...');
                generateQRCode(qrContainer, data.qr);
            } else {
                console.log('⚠️ QR Code não disponível');
                qrContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <h6>QR Code não disponível</h6>
                        <p>${data.message || 'Tente novamente em alguns segundos.'}</p>
                        <button class="btn btn-sm btn-primary" onclick="getQRCode()">Tentar Novamente</button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('❌ Erro ao obter QR Code:', error);
            qrContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h6>Erro ao carregar QR Code</h6>
                    <p>${error.message}</p>
                    <button class="btn btn-sm btn-primary" onclick="getQRCode()">Tentar Novamente</button>
                </div>
            `;
        });
}

// Gerar QR Code usando imagem do servidor
function generateQRCode(container, qrData) {
    console.log('🎨 Gerando QR Code via servidor...');
    
    // Usar a rota que gera imagem no servidor
    const imageUrl = '{{ route("whatsapp.qr-image") }}';
    
    container.innerHTML = `
        <img src="${imageUrl}?t=${Date.now()}" alt="QR Code" class="img-fluid" style="max-width: 256px;">
        <div class="mt-2">
            <small class="text-muted">Escaneie este QR Code com seu WhatsApp</small>
        </div>
    `;
    
    console.log('✅ QR Code carregado via servidor!');
}

// Iniciar verificação periódica
function startStatusCheck() {
    statusCheckInterval = setInterval(checkStatus, 5000);
    console.log('⏰ Verificação periódica iniciada (5s)');
}

// Parar verificação periódica
function stopStatusCheck() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        console.log('⏹️ Verificação periódica parada');
    }
}

// Função para forçar reconexão
function forceReconnect() {
    console.log('🔄 Iniciando processo de Reset/Reconectar...');
    
    const qrContainer = document.getElementById('qrCode');
    const statusElement = document.getElementById('whatsappStatus');
    
    // Mostrar loading
    statusElement.innerHTML = '<span class="badge bg-warning">Reconectando...</span>';
    qrContainer.innerHTML = '<div class="alert alert-info">🔄 Reconectando WhatsApp...</div>';
    
    // Primeiro, verificar se está conectado
    fetch('{{ route("whatsapp.status") }}')
        .then(response => response.json())
        .then(data => {
            console.log('📊 Status atual:', data);
            
            if (data.isConnected) {
                console.log('📱 WhatsApp conectado, desconectando primeiro...');
                // Se está conectado, desconectar primeiro
                return fetch('{{ route("whatsapp.disconnect") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            } else {
                console.log('📱 WhatsApp já desconectado, gerando novo QR Code...');
                return Promise.resolve();
            }
        })
        .then(() => {
            // Aguardar um pouco e verificar status novamente
            setTimeout(() => {
                console.log('⏳ Aguardando novo status...');
                checkStatus();
            }, 2000);
        })
        .catch(error => {
            console.error('❌ Erro no processo de reconexão:', error);
            statusElement.innerHTML = '<span class="badge bg-danger">Erro</span>';
            qrContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h6>Erro no processo de reconexão</h6>
                    <p>${error.message}</p>
                    <button class="btn btn-sm btn-primary" onclick="forceReconnect()">Tentar Novamente</button>
                </button>
            `;
        });
}

// Função para mostrar opções de emergência
function showEmergencyOptions() {
    const emergencyDiv = document.getElementById('emergencyOptions');
    if (emergencyDiv.style.display === 'none') {
        emergencyDiv.style.display = 'block';
    } else {
        emergencyDiv.style.display = 'none';
    }
}

// Teste de mensagem
document.getElementById('testMessageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phone = document.getElementById('testPhone').value;
    const message = document.getElementById('testMessage').value;
    
    console.log('📤 Enviando mensagem de teste...');
    
    fetch('{{ route("whatsapp.test-message") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            phone: phone,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Mensagem enviada com sucesso!');
        } else {
            alert('❌ Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('❌ Erro ao enviar mensagem:', error);
        alert('❌ Erro ao enviar mensagem');
    });
});
</script>
@endsection 