# 🚀 SendZap - Guia de Comandos

## 📋 Comandos Principais

### **🚀 Iniciar Tudo (Recomendado)**
```bash
./start.sh
```
**O que faz:**
- ✅ Inicia Laravel (porta 8000)
- ✅ Inicia WhatsApp Service (porta 3001)
- ✅ Inicia filas (queue worker)
- ✅ Verifica se tudo está funcionando
- ✅ Mostra URLs importantes

### **🛑 Parar Tudo**
```bash
./stop.sh
```
**O que faz:**
- 🛑 Para Laravel
- 🛑 Para WhatsApp Service
- 🛑 Para filas
- 🛑 Verifica se tudo foi parado

### **📊 Ver Status**
```bash
./status.sh
```
**O que mostra:**
- 📊 Status de todos os serviços
- 🌐 URLs importantes
- 📝 Localização dos logs
- 📱 Status do WhatsApp

### **🔄 Reiniciar Tudo**
```bash
./stop.sh && ./start.sh
```

## 🎯 Comandos Manuais (Se Precisar)

### **Iniciar Laravel Manualmente**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### **Iniciar WhatsApp Service Manualmente**
```bash
cd whatsapp-service
npm start
```

### **Iniciar Filas Manualmente**
```bash
php artisan queue:work --queue=default --tries=3 --timeout=3600
```

### **Verificar Status das Filas**
```bash
php artisan queue:monitor default
```

### **Limpar Jobs Falhados**
```bash
php artisan queue:flush
```

## 📱 URLs Importantes

| Função | URL |
|--------|-----|
| **Sistema Principal** | http://localhost:8000 |
| **Conectar WhatsApp** | http://localhost:8000/whatsapp/connect |
| **Campanhas** | http://localhost:8000/campaigns |
| **Disparar Campanhas** | http://localhost:8000/campaigns/dispatch/select |
| **Contatos** | http://localhost:8000/contacts |
| **Status WhatsApp** | http://localhost:3001/health |

## 📝 Logs

| Serviço | Arquivo |
|---------|---------|
| **Laravel** | `storage/logs/laravel.log` |
| **WhatsApp** | `storage/logs/whatsapp.log` |
| **Filas** | `storage/logs/queue.log` |

### **Ver Logs em Tempo Real**
```bash
# Laravel
tail -f storage/logs/laravel.log

# WhatsApp
tail -f storage/logs/whatsapp.log

# Filas
tail -f storage/logs/queue.log
```

## 🔧 Troubleshooting

### **Problema: Porta já em uso**
```bash
# Verificar o que está usando a porta
lsof -ti:8000
lsof -ti:3001

# Parar processo específico
kill -9 [PID]
```

### **Problema: WhatsApp não conecta**
```bash
# 1. Parar tudo
./stop.sh

# 2. Limpar autenticação
rm -rf whatsapp-service/auth_info_baileys

# 3. Iniciar tudo
./start.sh

# 4. Conectar WhatsApp
# Acesse: http://localhost:8000/whatsapp/connect
```

### **Problema: Filas não processam**
```bash
# Verificar status
php artisan queue:monitor default

# Reiniciar filas
php artisan queue:restart

# Ver logs
tail -f storage/logs/queue.log
```

## 🎯 Fluxo de Trabalho Recomendado

### **1. Iniciar o Sistema**
```bash
./start.sh
```

### **2. Conectar WhatsApp**
- Acesse: http://localhost:8000/whatsapp/connect
- Clique em "Limpar Autenticação" se necessário
- Escaneie o QR Code

### **3. Criar Campanhas**
- Acesse: http://localhost:8000/campaigns
- Clique em "Nova Campanha"
- Configure a mensagem

### **4. Disparar Campanhas**
- Acesse: http://localhost:8000/campaigns/dispatch/select
- Selecione a campanha
- Confirme o envio

### **5. Acompanhar Resultados**
- Acesse: http://localhost:8000/campaigns
- Clique na campanha
- Veja os relatórios

## 🚨 Dicas Importantes

### **✅ Sempre Use:**
- `./start.sh` para iniciar
- `./stop.sh` para parar
- `./status.sh` para verificar

### **❌ Evite:**
- Parar processos manualmente
- Deletar arquivos sem usar a interface
- Ignorar mensagens de erro

### **🔍 Se Algo Não Funcionar:**
1. Execute `./status.sh`
2. Verifique os logs
3. Reinicie com `./stop.sh && ./start.sh`
4. Se persistir, limpe a autenticação do WhatsApp

## 🎉 Pronto!

Agora você tem um sistema completo e organizado para gerenciar o SendZap! 🚀 