# WhatsApp Service - SendZap

Serviço Node.js para integração com WhatsApp usando Baileys.

## 🚀 Instalação

```bash
# Instalar dependências
npm install

# Copiar arquivo de configuração
cp env.example .env

# Iniciar o serviço
npm start
```

## 📋 Pré-requisitos

- Node.js 18+
- NPM ou Yarn
- WhatsApp instalado no celular

## ⚙️ Configuração

Edite o arquivo `.env`:

```env
# Porta do serviço
PORT=3001

# Ambiente
NODE_ENV=development

# Configurações do WhatsApp
WHATSAPP_BROWSER_NAME=SendZap
WHATSAPP_BROWSER_VERSION=1.0.0

# Rate Limiting
MESSAGE_DELAY=2000
MAX_MESSAGES_PER_MINUTE=30

# Logging
LOG_LEVEL=info
```

## 🔧 Uso

### Iniciar o serviço

```bash
# Desenvolvimento
npm run dev

# Produção
npm start
```

### Conectar WhatsApp

1. Acesse `http://localhost:3001/health` para verificar se o serviço está online
2. Acesse `http://localhost:3001/qr` para obter o QR Code
3. Escaneie o QR Code com o WhatsApp do seu celular
4. Verifique o status em `http://localhost:3001/status`

## 📡 API Endpoints

### Health Check
```
GET /health
```

### Status da Conexão
```
GET /status
```

### QR Code
```
GET /qr
```

### Enviar Mensagem
```
POST /send-message
{
  "phone": "+5511999999999",
  "message": "Olá!",
  "messageType": "text"
}
```

### Enviar Campanha
```
POST /send-campaign
{
  "messages": [
    {
      "phone": "+5511999999999",
      "message": "Mensagem 1"
    },
    {
      "phone": "+5511888888888", 
      "message": "Mensagem 2"
    }
  ]
}
```

### Desconectar
```
POST /disconnect
```

## 🧪 Testes

```bash
# Executar testes
npm test

# Executar testes com coverage
npm test -- --coverage
```

## 📁 Estrutura de Arquivos

```
whatsapp-service/
├── server.js          # Servidor principal
├── package.json       # Dependências
├── env.example        # Configurações de exemplo
├── jest.config.js     # Configuração do Jest
├── jest.setup.js      # Setup do Jest
├── server.test.js     # Testes do servidor
└── auth_info_baileys/ # Dados de autenticação (gerado automaticamente)
```

## 🔒 Segurança

- O arquivo `auth_info_baileys/` contém dados sensíveis de autenticação
- Não compartilhe este diretório
- Use HTTPS em produção
- Implemente rate limiting adequado

## ⚠️ Limitações

- WhatsApp pode detectar automação e bloquear o número
- Mantenha o número conectado
- Respeite os limites de envio
- Use delays entre mensagens

## 🐛 Troubleshooting

### Serviço não inicia
- Verifique se a porta 3001 está livre
- Verifique se o Node.js está instalado

### QR Code não aparece
- Reinicie o serviço
- Verifique os logs
- Aguarde alguns segundos

### Mensagens não enviam
- Verifique se o WhatsApp está conectado
- Verifique o formato do telefone
- Verifique os logs de erro

## 📞 Suporte

Para dúvidas ou problemas, consulte:
- [Documentação do Baileys](https://github.com/whiskeysockets/baileys)
- [Issues do projeto](https://github.com/seu-usuario/sendzap/issues) 