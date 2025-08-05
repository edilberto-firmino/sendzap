#!/bin/bash

echo "🛑 Parando SendZap - Sistema Completo"
echo "====================================="

# Função para parar processos por porta
stop_by_port() {
    local port=$1
    local service_name=$2
    
    PIDS=$(lsof -ti:$port 2>/dev/null)
    if [ ! -z "$PIDS" ]; then
        echo "🛑 Parando $service_name (porta $port)..."
        echo "$PIDS" | xargs kill -9
        echo "✅ $service_name parado"
    else
        echo "ℹ️  $service_name não estava rodando"
    fi
}

# Função para parar processos por nome
stop_by_name() {
    local process_name=$1
    local service_name=$2
    
    PIDS=$(ps aux | grep "$process_name" | grep -v grep | awk '{print $2}')
    if [ ! -z "$PIDS" ]; then
        echo "🛑 Parando $service_name..."
        echo "$PIDS" | xargs kill -9
        echo "✅ $service_name parado"
    else
        echo "ℹ️  $service_name não estava rodando"
    fi
}

# 1. Parar Laravel
stop_by_port 8000 "Laravel"

# 2. Parar WhatsApp Service
stop_by_port 3001 "WhatsApp Service"

# 3. Parar filas
stop_by_name "queue:work" "Filas"

# 4. Parar outros processos relacionados
stop_by_name "php artisan serve" "Laravel (processos extras)"
stop_by_name "node server.js" "WhatsApp Service (processos extras)"

echo ""
echo "🔍 Verificando se tudo foi parado..."

sleep 2

# Verificar status final
echo ""
echo "📊 Status Final:"
echo "================"

if lsof -ti:8000 > /dev/null 2>&1; then
    echo "❌ Laravel ainda está rodando na porta 8000"
else
    echo "✅ Laravel parado"
fi

if lsof -ti:3001 > /dev/null 2>&1; then
    echo "❌ WhatsApp Service ainda está rodando na porta 3001"
else
    echo "✅ WhatsApp Service parado"
fi

QUEUE_COUNT=$(ps aux | grep "queue:work" | grep -v grep | wc -l)
if [ $QUEUE_COUNT -gt 0 ]; then
    echo "❌ Filas ainda estão rodando ($QUEUE_COUNT processos)"
else
    echo "✅ Filas paradas"
fi

echo ""
echo "🎉 SendZap parado com sucesso!"
echo "===============================" 