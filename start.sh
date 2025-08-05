#!/bin/bash

echo "🚀 Iniciando SendZap - Sistema Completo"
echo "========================================"

# Função para verificar se uma porta está em uso
check_port() {
    lsof -ti:$1 > /dev/null 2>&1
}

# Função para aguardar serviço ficar pronto
wait_for_service() {
    local port=$1
    local service_name=$2
    local max_attempts=30
    local attempt=1
    
    echo "⏳ Aguardando $service_name ficar pronto..."
    
    while [ $attempt -le $max_attempts ]; do
        if check_port $port; then
            echo "✅ $service_name está rodando na porta $port"
            return 0
        fi
        
        echo "   Tentativa $attempt/$max_attempts..."
        sleep 2
        ((attempt++))
    done
    
    echo "❌ $service_name não ficou pronto em $max_attempts tentativas"
    return 1
}

# 1. Verificar se já está rodando
echo "🔍 Verificando serviços existentes..."

if check_port 8000; then
    echo "⚠️  Laravel já está rodando na porta 8000"
else
    echo "📱 Iniciando Laravel..."
    php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/laravel.log 2>&1 &
    LARAVEL_PID=$!
    echo "   Laravel iniciado com PID: $LARAVEL_PID"
fi

if check_port 3001; then
    echo "⚠️  WhatsApp Service já está rodando na porta 3001"
else
    echo "📱 Iniciando WhatsApp Service..."
    cd whatsapp-service
    npm start > ../storage/logs/whatsapp.log 2>&1 &
    WHATSAPP_PID=$!
    cd ..
    echo "   WhatsApp Service iniciado com PID: $WHATSAPP_PID"
fi

# 2. Aguardar serviços ficarem prontos
echo ""
echo "⏳ Aguardando serviços ficarem prontos..."

if ! check_port 8000; then
    wait_for_service 8000 "Laravel"
fi

if ! check_port 3001; then
    wait_for_service 3001 "WhatsApp Service"
fi

# 3. Iniciar filas
echo ""
echo "🔄 Iniciando filas..."

# Verificar se as filas já estão rodando
QUEUE_PROCESSES=$(ps aux | grep "queue:work" | grep -v grep | wc -l)

if [ $QUEUE_PROCESSES -eq 0 ]; then
    echo "📋 Iniciando worker das filas..."
    php artisan queue:work --queue=default --tries=3 --timeout=3600 > storage/logs/queue.log 2>&1 &
    QUEUE_PID=$!
    echo "   Filas iniciadas com PID: $QUEUE_PID"
else
    echo "⚠️  Filas já estão rodando ($QUEUE_PROCESSES processos)"
fi

# 4. Verificar status final
echo ""
echo "🔍 Verificando status final..."

sleep 3

echo ""
echo "📊 Status dos Serviços:"
echo "======================="

if check_port 8000; then
    echo "✅ Laravel: http://localhost:8000"
else
    echo "❌ Laravel: Não está rodando"
fi

if check_port 3001; then
    echo "✅ WhatsApp Service: http://localhost:3001"
else
    echo "❌ WhatsApp Service: Não está rodando"
fi

QUEUE_COUNT=$(ps aux | grep "queue:work" | grep -v grep | wc -l)
if [ $QUEUE_COUNT -gt 0 ]; then
    echo "✅ Filas: $QUEUE_COUNT processo(s) rodando"
else
    echo "❌ Filas: Não estão rodando"
fi

echo ""
echo "🎯 URLs Importantes:"
echo "===================="
echo "📱 Sistema: http://localhost:8000"
echo "📱 WhatsApp: http://localhost:8000/whatsapp/connect"
echo "📱 Campanhas: http://localhost:8000/campaigns"
echo "📱 Contatos: http://localhost:8000/contacts"
echo "📱 Disparar: http://localhost:8000/campaigns/dispatch/select"

echo ""
echo "📝 Logs disponíveis em:"
echo "======================"
echo "📋 Laravel: storage/logs/laravel.log"
echo "📱 WhatsApp: storage/logs/whatsapp.log"
echo "🔄 Filas: storage/logs/queue.log"

echo ""
echo "🎉 SendZap iniciado com sucesso!"
echo "=================================" 