# SendZap - Sistema de Envio de Mensagens WhatsApp

Sistema completo para gerenciamento de contatos e envio de campanhas via WhatsApp usando Laravel e Baileys.

## 🚀 Funcionalidades

- **Gestão de Contatos**: CRUD completo com importação em lote
- **Campanhas de Mensagens**: Criação e envio de campanhas personalizadas
- **Integração WhatsApp**: Conexão via QR Code usando Baileys
- **Relatórios**: Acompanhamento de status de envio
- **Variáveis Personalizadas**: Substituição automática de dados nos contatos
- **Envio em Background**: Jobs para processamento assíncrono

## 📋 Pré-requisitos

- PHP 8.2+
- Laravel 12
- Node.js 18+
- MySQL/PostgreSQL
- WhatsApp instalado no celular

## ⚙️ Instalação

### 1. Clonar e configurar o projeto Laravel

```bash
git clone <repository-url>
cd sendzap
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### 2. Instalar e configurar o serviço WhatsApp

```bash
# Executar script de instalação
./install-whatsapp-service.sh

# Ou instalar manualmente
cd whatsapp-service
npm install
cp env.example .env
npm start
```

### 3. Configurar variáveis de ambiente

Adicione ao seu `.env`:

```env
# WhatsApp Service
WHATSAPP_SERVICE_URL=http://localhost:3001
WHATSAPP_SERVICE_TIMEOUT=30
WHATSAPP_MESSAGE_DELAY=2
WHATSAPP_MAX_RETRIES=3
```

## 🔧 Uso

### 1. Conectar WhatsApp

1. Acesse `http://localhost:8000/whatsapp/connect`
2. Verifique se o serviço está online
3. Escaneie o QR Code com seu WhatsApp
4. Teste o envio de uma mensagem

### 2. Gerenciar Contatos

- **Listar**: `http://localhost:8000/contacts`
- **Criar**: `http://localhost:8000/contacts/create`
- **Importar**: `http://localhost:8000/contacts/import`

### 3. Criar Campanhas

- **Listar**: `http://localhost:8000/campaigns`
- **Criar**: `http://localhost:8000/campaigns/create`
- **Disparar**: Acesse a campanha e clique em "Disparar"

### 4. Variáveis Disponíveis

Use estas variáveis nas mensagens das campanhas:

- `{nome}` - Nome do contato
- `{nome_social}` - Nome social
- `{cidade}` - Cidade
- `{estado}` - Estado
- `{idade}` - Idade
- `{genero}` - Gênero
- `{telefone}` - Telefone
- `{email}` - Email

## 🧪 Testes

### Executar testes do Laravel

```bash
php artisan test --env=testing
```

### Executar testes do serviço WhatsApp

```bash
cd whatsapp-service
npm test
```

## 📁 Estrutura do Projeto

```
sendzap/
├── app/
│   ├── Http/Controllers/
│   │   ├── WhatsAppController.php
│   │   ├── CampaignController.php
│   │   └── ContactController.php
│   ├── Jobs/
│   │   └── SendCampaignJob.php
│   ├── Models/
│   │   ├── Campaign.php
│   │   ├── CampaignLog.php
│   │   └── Contact.php
│   └── Services/
│       └── WhatsAppService.php
├── whatsapp-service/
│   ├── server.js
│   ├── package.json
│   └── README.md
├── resources/views/
│   ├── whatsapp/
│   │   └── connect.blade.php
│   ├── campaigns/
│   └── contacts/
└── tests/
    └── Feature/
        ├── WhatsAppServiceTest.php
        └── SendCampaignJobTest.php
```

## 🔒 Segurança

- O arquivo `auth_info_baileys/` contém dados sensíveis
- Não compartilhe este diretório
- Use HTTPS em produção
- Implemente rate limiting adequado

## ⚠️ Limitações

- WhatsApp pode detectar automação e bloquear o número
- Mantenha o número conectado
- Respeite os limites de envio
- Use delays entre mensagens

## 🐛 Troubleshooting

### Serviço WhatsApp não inicia
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

### Jobs não processam
- Verifique se o queue worker está rodando
- Execute: `php artisan queue:work`

## 📞 Suporte

Para dúvidas ou problemas:
- [Documentação do Baileys](https://github.com/whiskeysockets/baileys)
- [Issues do projeto](https://github.com/seu-usuario/sendzap/issues)

## 📄 Licença

MIT License - veja o arquivo [LICENSE](LICENSE) para detalhes.
