import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/e2e',
  timeout: 60_000,
  retries: 1,
  reporter: [['html', { outputFolder: 'playwright-report' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://127.0.0.1:8000',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'on-first-retry'
  },
  webServer: {
    command: process.platform === 'win32'
      ? 'cmd /c "set CI_AUTH_BYPASS=1 && php -S 127.0.0.1:8000 -t ."'
      : 'bash -lc "CI_AUTH_BYPASS=1 php -S 127.0.0.1:8000 -t ."',
    url: 'http://127.0.0.1:8000',
    reuseExistingServer: true,
    timeout: 30_000
  },
  projects: [
    { name: 'chromium-desktop', use: { ...devices['Desktop Chrome'] } },
    { name: 'webkit-desktop', use: { ...devices['Desktop Safari'] } },
    { name: 'mobile', use: { ...devices['Pixel 5'] } }
  ]
});
