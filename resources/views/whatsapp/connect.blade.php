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
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Status do Serviço -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Status do Serviço</h6>
                                    <div id="serviceStatus">
                                        <span class="badge bg-{{ $isOnline ? 'success' : 'danger' }}">
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Status WhatsApp</h6>
                                    <div id="whatsappStatus">
                                        <span class="badge bg-{{ $status['isConnected'] ? 'success' : 'warning' }}">
                                            {{ $status['isConnected'] ? 'Conectado' : 'Desconectado' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div id="qrCodeSection" class="text-center mb-4" style="display: {{ $status['status'] === 'qr_ready' ? 'block' : 'none' }};">
                        <h5>Escaneie o QR Code</h5>
                        <p class="text-muted">Abra o WhatsApp no seu celular e escaneie o código abaixo</p>
                        <div id="qrCode" class="mb-3"></div>
                        <button class="btn btn-primary" onclick="refreshQR()">
                            <i class="fas fa-sync-alt"></i> Atualizar QR Code
                        </button>
                    </div>

                    <!-- Status Conectado -->
                    <div id="connectedSection" class="text-center mb-4" style="display: {{ $status['isConnected'] ? 'block' : 'none' }};">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> WhatsApp Conectado!</h5>
                            <p>Seu WhatsApp está conectado e pronto para enviar mensagens.</p>
                        </div>
                    </div>

                    <!-- Mensagem de Teste -->
                    <div id="testMessageSection" class="mb-4" style="display: {{ $status['isConnected'] ? 'block' : 'none' }};">
                        <h5>Enviar Mensagem de Teste</h5>
                        <form id="testMessageForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="testPhone" class="form-label">Telefone</label>
                                        <input type="text" class="form-control" id="testPhone" name="phone" 
                                               placeholder="+5511999999999" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="testMessage" class="form-label">Mensagem</label>
                                        <input type="text" class="form-control" id="testMessage" name="message" 
                                               placeholder="Mensagem de teste" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Enviar Teste
                            </button>
                        </form>
                        <div id="testResult" class="mt-3"></div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="text-center">
                        <button class="btn btn-info me-2" onclick="checkStatus()">
                            <i class="fas fa-sync-alt"></i> Verificar Status
                        </button>
                        
                        @if($status['isConnected'])
                            <form action="{{ route('whatsapp.disconnect') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger me-2" onclick="return confirm('Tem certeza que deseja desconectar?')">
                                    <i class="fas fa-times"></i> Desconectar
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('whatsapp.clear-auth') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Isso irá limpar todos os dados de autenticação e forçar uma nova conexão. Tem certeza?')">
                                <i class="fas fa-trash"></i> Limpar Autenticação
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<script>
let statusCheckInterval;

// Verificar status periodicamente
function startStatusCheck() {
    statusCheckInterval = setInterval(checkStatus, 5000); // 5 segundos
}

function stopStatusCheck() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
}

// Verificar status do WhatsApp
function checkStatus() {
    fetch('{{ route("whatsapp.status") }}')
        .then(response => response.json())
        .then(data => {
            updateStatusDisplay(data);
        })
        .catch(error => {
            console.error('Erro ao verificar status:', error);
        });
}

// Atualizar display do status
function updateStatusDisplay(status) {
    const whatsappStatus = document.getElementById('whatsappStatus');
    const qrCodeSection = document.getElementById('qrCodeSection');
    const connectedSection = document.getElementById('connectedSection');
    const testMessageSection = document.getElementById('testMessageSection');

    // Atualizar badge do status
    whatsappStatus.innerHTML = `
        <span class="badge bg-${status.isConnected ? 'success' : 'warning'}">
            ${status.isConnected ? 'Conectado' : 'Desconectado'}
        </span>
    `;

    // Mostrar/ocultar seções baseado no status
    if (status.status === 'qr_ready') {
        qrCodeSection.style.display = 'block';
        connectedSection.style.display = 'none';
        testMessageSection.style.display = 'none';
        getQRCode();
    } else if (status.isConnected) {
        qrCodeSection.style.display = 'none';
        connectedSection.style.display = 'block';
        testMessageSection.style.display = 'block';
        stopStatusCheck(); // Parar verificação quando conectado
    } else {
        qrCodeSection.style.display = 'none';
        connectedSection.style.display = 'none';
        testMessageSection.style.display = 'none';
    }
}

// Obter QR Code
function getQRCode() {
    fetch('{{ route("whatsapp.qr") }}')
        .then(response => response.json())
        .then(data => {
            if (data.qr) {
                const qrContainer = document.getElementById('qrCode');
                qrContainer.innerHTML = '';
                QRCode.toCanvas(qrContainer, data.qr, { width: 256 }, function (error) {
                    if (error) console.error(error);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao obter QR Code:', error);
        });
}

// Atualizar QR Code
function refreshQR() {
    getQRCode();
}

// Enviar mensagem de teste
document.getElementById('testMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultDiv = document.getElementById('testResult');
    
    resultDiv.innerHTML = '<div class="alert alert-info">Enviando mensagem...</div>';
    
    fetch('{{ route("whatsapp.test-message") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            phone: formData.get('phone'),
            message: formData.get('message')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            this.reset();
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger">Erro ao enviar mensagem</div>';
        console.error('Erro:', error);
    });
});

// Iniciar verificação de status quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    checkStatus();
    
    // Se não estiver conectado, iniciar verificação periódica
    if (!{{ $status['isConnected'] ? 'true' : 'false' }}) {
        startStatusCheck();
    }
});
</script>
@endsection 