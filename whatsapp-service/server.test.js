const request = require('supertest');
const app = require('./server');

describe('WhatsApp Service API', () => {
    describe('GET /health', () => {
        it('should return health status', async () => {
            const response = await request(app)
                .get('/health')
                .expect(200);

            expect(response.body).toHaveProperty('status', 'ok');
            expect(response.body).toHaveProperty('timestamp');
            expect(response.body).toHaveProperty('whatsapp');
        });
    });

    describe('GET /status', () => {
        it('should return connection status', async () => {
            const response = await request(app)
                .get('/status')
                .expect(200);

            expect(response.body).toHaveProperty('status');
            expect(response.body).toHaveProperty('isConnected');
            expect(response.body).toHaveProperty('timestamp');
        });
    });

    describe('GET /qr', () => {
        it('should return QR code status', async () => {
            const response = await request(app)
                .get('/qr')
                .expect(200);

            expect(response.body).toHaveProperty('status');
            expect(response.body).toHaveProperty('qr');
        });
    });

    describe('POST /send-message', () => {
        it('should return error when WhatsApp is not connected', async () => {
            const response = await request(app)
                .post('/send-message')
                .send({
                    phone: '+5511999999999',
                    message: 'Test message'
                })
                .expect(400);

            expect(response.body).toHaveProperty('success', false);
            expect(response.body).toHaveProperty('error');
        });

        it('should return error when phone is missing', async () => {
            const response = await request(app)
                .post('/send-message')
                .send({
                    message: 'Test message'
                })
                .expect(400);

            expect(response.body).toHaveProperty('success', false);
            expect(response.body.error).toContain('Telefone e mensagem são obrigatórios');
        });

        it('should return error when message is missing', async () => {
            const response = await request(app)
                .post('/send-message')
                .send({
                    phone: '+5511999999999'
                })
                .expect(400);

            expect(response.body).toHaveProperty('success', false);
            expect(response.body.error).toContain('Telefone e mensagem são obrigatórios');
        });
    });

    describe('POST /send-campaign', () => {
        it('should return error when WhatsApp is not connected', async () => {
            const response = await request(app)
                .post('/send-campaign')
                .send({
                    messages: [
                        { phone: '+5511999999999', message: 'Test message' }
                    ]
                })
                .expect(400);

            expect(response.body).toHaveProperty('success', false);
            expect(response.body).toHaveProperty('error');
        });

        it('should return error when messages array is missing', async () => {
            const response = await request(app)
                .post('/send-campaign')
                .send({})
                .expect(400);

            expect(response.body).toHaveProperty('success', false);
            expect(response.body.error).toContain('Lista de mensagens é obrigatória');
        });

        it('should return error when messages array is empty', async () => {
            const response = await request(app)
                .post('/send-campaign')
                .send({
                    messages: []
                })
                .expect(400);

            expect(response.body).toHaveProperty('success', false);
            expect(response.body.error).toContain('Lista de mensagens é obrigatória');
        });
    });

    describe('POST /disconnect', () => {
        it('should return success message', async () => {
            const response = await request(app)
                .post('/disconnect')
                .expect(200);

            expect(response.body).toHaveProperty('success', true);
            expect(response.body).toHaveProperty('message');
        });
    });
}); 