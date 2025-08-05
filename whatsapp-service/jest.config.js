module.exports = {
    testEnvironment: 'node',
    testMatch: ['**/*.test.js'],
    collectCoverage: true,
    coverageDirectory: 'coverage',
    coverageReporters: ['text', 'lcov', 'html'],
    coveragePathIgnorePatterns: [
        '/node_modules/',
        '/coverage/',
        '/auth_info_baileys/'
    ],
    setupFilesAfterEnv: ['./jest.setup.js']
}; 