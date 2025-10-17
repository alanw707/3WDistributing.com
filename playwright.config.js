// @ts-check
module.exports = {
  testDir: './tests/e2e',
  timeout: 30 * 1000,
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:8080',
    headless: true,
  },
  reporter: [['list'], ['html', { outputFolder: 'test-results' }]],
};
