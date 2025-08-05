const { default: makeWASocket, DisconnectReason, useMultiFileAuthState } = require('@whiskeysockets/baileys');
const qrcode = require('qrcode-terminal');
const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Estado global
let sock = null;
let qrCode = null;
let connectionStatus = 'disconnected';
let isConnected = false;

// Função para conectar ao WhatsApp
async function connectToWhatsApp() {
    try {
        const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');
        
        sock = makeWASocket({
            auth: state,
            printQRInTerminal: true,
            browser: ['SendZap', 'Chrome', '1.0.0'],
        });

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;
            
            if (qr) {
                qrCode = qr;
                connectionStatus = 'qr_ready';
                console.log('QR Code gerado!');
                qrcode.generate(qr, { small: true });
            }
            
            if (connection === 'open') {
                connectionStatus = 'connected';
                isConnected = true;
                qrCode = null;
                console.log('WhatsApp conectado com sucesso!');
            }
            
            if (connection === 'close') {
                connectionStatus = 'disconnected';
                isConnected = false;
                const shouldReconnect = (lastDisconnect?.error)?.output?.statusCode !== DisconnectReason.loggedOut;
                if (shouldReconnect) {
                    console.log('Reconectando...');
                    setTimeout(connectToWhatsApp, 5000);
                } else {
                    console.log('Desconectado do WhatsApp');
                }
            }
        });

        sock.ev.on('creds.update', saveCreds);
        
    } catch (error) {
        console.error('Erro ao conectar:', error);
        connectionStatus = 'error';
    }
}

// API Routes

// Status da conexão
app.get('/status', (req, res) => {
    res.json({
        status: connectionStatus,
        isConnected,
        timestamp: new Date().toISOString()
    });
});

// Obter QR Code
app.get('/qr', (req, res) => {
    if (qrCode && connectionStatus === 'qr_ready') {
        res.json({ 
            qr: qrCode,
            status: 'qr_ready'
        });
    } else if (isConnected) {
        res.json({ 
            qr: null,
            status: 'connected',
            message: 'WhatsApp já está conectado'
        });
    } else {
        res.json({ 
            qr: null,
            status: connectionStatus,
            message: 'QR Code não disponível'
        });
    }
});

// Enviar mensagem
app.post('/send-message', async (req, res) => {
    const { phone, message, campaignId, messageType = 'text' } = req.body;
    
    if (!isConnected || !sock) {
        return res.status(400).json({
            success: false,
            error: 'WhatsApp não está conectado'
        });
    }
    
    if (!phone || !message) {
        return res.status(400).json({
            success: false,
            error: 'Telefone e mensagem são obrigatórios'
        });
    }
    
    try {
        // Formatar telefone
        let formattedPhone = phone.replace(/\D/g, '');
        if (!formattedPhone.startsWith('55')) {
            formattedPhone = '55' + formattedPhone;
        }
        formattedPhone = formattedPhone + '@s.whatsapp.net';
        
        // Preparar mensagem baseada no tipo
        let messageContent = {};
        
        switch (messageType) {
            case 'text':
                messageContent = { text: message };
                break;
            case 'image':
                messageContent = {
                    image: { url: message },
                    caption: req.body.caption || ''
                };
                break;
            case 'document':
                messageContent = {
                    document: { url: message },
                    mimetype: req.body.mimetype || 'application/pdf',
                    fileName: req.body.fileName || 'documento'
                };
                break;
            default:
                messageContent = { text: message };
        }
        
        // Enviar mensagem
        const result = await sock.sendMessage(formattedPhone, messageContent);
        
        res.json({
            success: true,
            messageId: result.key.id,
            status: 'sent',
            timestamp: new Date().toISOString()
        });
        
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        res.status(500).json({
            success: false,
            error: error.message,
            status: 'failed'
        });
    }
});

// Enviar múltiplas mensagens (para campanhas)
app.post('/send-campaign', async (req, res) => {
    const { messages } = req.body;
    
    if (!isConnected || !sock) {
        return res.status(400).json({
            success: false,
            error: 'WhatsApp não está conectado'
        });
    }
    
    if (!Array.isArray(messages) || messages.length === 0) {
        return res.status(400).json({
            success: false,
            error: 'Lista de mensagens é obrigatória'
        });
    }
    
    const results = [];
    
    for (const msg of messages) {
        try {
            let formattedPhone = msg.phone.replace(/\D/g, '');
            if (!formattedPhone.startsWith('55')) {
                formattedPhone = '55' + formattedPhone;
            }
            formattedPhone = formattedPhone + '@s.whatsapp.net';
            
            const result = await sock.sendMessage(formattedPhone, { text: msg.message });
            
            results.push({
                phone: msg.phone,
                success: true,
                messageId: result.key.id,
                status: 'sent'
            });
            
            // Delay para evitar bloqueio
            await new Promise(resolve => setTimeout(resolve, 2000));
            
        } catch (error) {
            results.push({
                phone: msg.phone,
                success: false,
                error: error.message,
                status: 'failed'
            });
        }
    }
    
    res.json({
        success: true,
        results,
        total: messages.length,
        sent: results.filter(r => r.success).length,
        failed: results.filter(r => !r.success).length
    });
});

// Desconectar WhatsApp
app.post('/disconnect', async (req, res) => {
    try {
        if (sock) {
            await sock.logout();
            sock = null;
            isConnected = false;
            connectionStatus = 'disconnected';
        }
        
        res.json({
            success: true,
            message: 'WhatsApp desconectado com sucesso'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        whatsapp: {
            connected: isConnected,
            status: connectionStatus
        }
    });
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`WhatsApp Service rodando na porta ${PORT}`);
    console.log(`Health check: http://localhost:${PORT}/health`);
    console.log(`Status: http://localhost:${PORT}/status`);
    console.log(`QR Code: http://localhost:${PORT}/qr`);
    
    // Conectar ao WhatsApp
    connectToWhatsApp();
});

module.exports = app; 