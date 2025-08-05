// Configurações globais para os testes
jest.setTimeout(10000); // 10 segundos de timeout

// Mock do console para evitar logs durante os testes
global.console = {
    ...console,
    log: jest.fn(),
    error: jest.fn(),
    warn: jest.fn(),
    info: jest.fn(),
}; 