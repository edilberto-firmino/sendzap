#!/bin/bash

echo "🚀 Instalando WhatsApp Service para SendZap..."

# Verificar se Node.js está instalado
if ! command -v node &> /dev/null; then
    echo "❌ Node.js não está instalado. Por favor, instale o Node.js 18+ primeiro."
    exit 1
fi

# Verificar versão do Node.js
NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 18 ]; then
    echo "❌ Node.js versão 18+ é necessária. Versão atual: $(node -v)"
    exit 1
fi

echo "✅ Node.js $(node -v) encontrado"

# Entrar no diretório do serviço
cd whatsapp-service

# Instalar dependências
echo "📦 Instalando dependências..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Erro ao instalar dependências"
    exit 1
fi

# Copiar arquivo de configuração
if [ ! -f .env ]; then
    echo "⚙️ Copiando arquivo de configuração..."
    cp env.example .env
    echo "✅ Arquivo .env criado. Edite conforme necessário."
fi

# Criar diretório para logs
mkdir -p logs

echo "✅ WhatsApp Service instalado com sucesso!"
echo ""
echo "📋 Próximos passos:"
echo "1. Edite o arquivo whatsapp-service/.env se necessário"
echo "2. Inicie o serviço: cd whatsapp-service && npm start"
echo "3. Acesse http://localhost:3001/health para verificar se está online"
echo "4. Acesse http://localhost:3001/qr para obter o QR Code"
echo "5. Conecte o WhatsApp através do QR Code"
echo ""
echo "🔗 Links úteis:"
echo "- Status: http://localhost:3001/status"
echo "- Health: http://localhost:3001/health"
echo "- QR Code: http://localhost:3001/qr"
echo ""
echo "📚 Documentação: whatsapp-service/README.md" 