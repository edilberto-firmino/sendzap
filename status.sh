#!/bin/bash

echo "📊 Status do SendZap"
echo "===================="

# Função para verificar porta
check_port() {
    local port=$1
    local service_name=$2
    
    if lsof -ti:$port > /dev/null 2>&1; then
        echo "✅ $service_name: Rodando na porta $port"
        return 0
    else
        echo "❌ $service_name: Não está rodando"
        return 1
    fi
}

# Função para verificar processos
check_process() {
    local process_name=$1
    local service_name=$2
    
    COUNT=$(ps aux | grep "$process_name" | grep -v grep | wc -l)
    if [ $COUNT -gt 0 ]; then
        echo "✅ $service_name: $COUNT processo(s) rodando"
        return 0
    else
        echo "❌ $service_name: Não está rodando"
        return 1
    fi
}

# Verificar serviços
echo ""
echo "🔧 Serviços:"
echo "============"

check_port 8000 "Laravel"
check_port 3001 "WhatsApp Service"
check_process "queue:work" "Filas"

# Verificar URLs
echo ""
echo "🌐 URLs:"
echo "========"
echo "📱 Sistema: http://localhost:8000"
echo "📱 WhatsApp: http://localhost:8000/whatsapp/connect"
echo "📱 Campanhas: http://localhost:8000/campaigns"
echo "📱 Contatos: http://localhost:8000/contacts"
echo "📱 Disparar: http://localhost:8000/campaigns/dispatch/select"

# Verificar logs
echo ""
echo "📝 Logs:"
echo "========"
if [ -f "storage/logs/laravel.log" ]; then
    echo "📋 Laravel: storage/logs/laravel.log"
else
    echo "❌ Laravel: Log não encontrado"
fi

if [ -f "storage/logs/whatsapp.log" ]; then
    echo "📱 WhatsApp: storage/logs/whatsapp.log"
else
    echo "❌ WhatsApp: Log não encontrado"
fi

if [ -f "storage/logs/queue.log" ]; then
    echo "🔄 Filas: storage/logs/queue.log"
else
    echo "❌ Filas: Log não encontrado"
fi

# Verificar status do WhatsApp
echo ""
echo "📱 Status do WhatsApp:"
echo "======================"

if check_port 3001 "WhatsApp Service"; then
    STATUS=$(curl -s http://localhost:3001/status 2>/dev/null | jq -r '.status // "error"' 2>/dev/null || echo "error")
    CONNECTED=$(curl -s http://localhost:3001/status 2>/dev/null | jq -r '.isConnected // false' 2>/dev/null || echo "false")
    
    echo "   Status: $STATUS"
    if [ "$CONNECTED" = "true" ]; then
        echo "   Conectado: ✅ Sim"
    else
        echo "   Conectado: ❌ Não"
    fi
else
    echo "   Status: Serviço não está rodando"
fi

echo ""
echo "🎯 Comandos Úteis:"
echo "=================="
echo "🚀 Iniciar tudo: ./start.sh"
echo "🛑 Parar tudo: ./stop.sh"
echo "📊 Ver status: ./status.sh"
echo "🔄 Reiniciar: ./stop.sh && ./start.sh" 